<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterItem;

class MasterItemsController extends Controller
{
    /**
     * Display the master items dashboard with dynamic category counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get selected category from request or default to first category
        $selectedCategory = $request->get('category', 'Shirt Products');
        
        // Calculate dynamic category counts from database
        // Using exact category names from the view
        $categoryCounts = [
            'shirt' => MasterItem::where('category', 'Shirt Products')->count(),
            'uncategorized' => MasterItem::where('category', 'Uncategorized')->count(),
            'machines' => MasterItem::where('category', 'Machine and Equipments')->count(),
            'materials' => MasterItem::where('category', 'Garment Materials')->count(),
            'printing' => MasterItem::where('category', 'Printing and Office Supplies')->count(),
        ];

        // Calculate total active items (excluding soft-deleted)
        $totalActiveItems = MasterItem::count();

        // Calculate total items including soft-deleted for reference
        $totalItemsIncludingDeleted = MasterItem::withTrashed()->count();
        
        // Get master items filtered by selected category
        $masterItems = MasterItem::where('category', $selectedCategory)
            ->orderBy('name')
            ->get();

        // Pass all data to the view
        return view('master-items.index', [
            'categoryCounts' => $categoryCounts,
            'totalActiveItems' => $totalActiveItems,
            'totalItemsIncludingDeleted' => $totalItemsIncludingDeleted,
            'selectedCategory' => $selectedCategory,
            'masterItems' => $masterItems,
        ]);
    }
    
    /**
     * Show the form for creating a new master item.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('master-items.create');
    }
    
    /**
     * Store a newly created master item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Check if bulk creation is requested (selected_sizes array)
        $selectedSizes = $request->input('selected_sizes', []);
        
        if (!empty($selectedSizes) && is_array($selectedSizes)) {
            // ==================== BULK CREATION ====================
            $createdCount = 0;
            $errors = [];
            
            // Base data for all items - ONLY fields that exist in database
            $baseData = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'unit_price' => 'nullable|numeric|min:0',
                'sku' => 'nullable|string|max:50|unique:master_items,sku,NULL,id,deleted_at,NULL',
                'barcode' => 'nullable|string|max:100',
            ]);
            
            // Add created_by from authenticated user
            $baseData['created_by'] = auth()->id();
            
            // Create one product for each selected size
            foreach ($selectedSizes as $size) {
                try {
                    // Generate SKU: BRAND-TYPE-COLOR-SIZE (allow partial)
                    $brand = $request->input('brand', '');
                    $type = $request->input('type', '');
                    $color = $request->input('color', '');
                    
                    $skuParts = [];
                    if (!empty($brand)) $skuParts[] = strtoupper($brand);
                    if (!empty($type)) $skuParts[] = strtoupper($type);
                    if (!empty($color)) $skuParts[] = strtoupper($color);
                    
                    $sku = '';
                    if (!empty($skuParts)) {
                        $sku = implode('-', $skuParts) . '-' . $size;
                    }
                    
                    // Create product data - store size in description for now
                    $productData = array_merge($baseData, [
                        'sku' => $sku ?: null,
                    ]);
                    
                    // Add size to description if we have other details
                    $description = $request->input('description', '');
                    if (!empty($brand) || !empty($type) || !empty($color)) {
                        $sizeInfo = "Size: {$size}";
                        if (!empty($brand)) $sizeInfo .= ", Brand: {$brand}";
                        if (!empty($type)) $sizeInfo .= ", Type: {$type}";
                        if (!empty($color)) $sizeInfo .= ", Color: {$color}";
                        
                        $productData['description'] = $description ? $description . "\n" . $sizeInfo : $sizeInfo;
                    }
                    
                    // Create the product
                    MasterItem::create($productData);
                    $createdCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to create product for size {$size}: " . $e->getMessage();
                }
            }
            
            // Return success message with count
            $message = "Created {$createdCount} products successfully!";
            if (!empty($errors)) {
                $message .= " (Errors: " . count($errors) . ")";
            }
            
            return redirect()->route('master-items.index')
                ->with('success', $message);
            
        } else {
            // ==================== SINGLE ITEM CREATION ====================
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'unit_price' => 'nullable|numeric|min:0',
                'sku' => 'nullable|string|max:50|unique:master_items,sku,NULL,id,deleted_at,NULL',
                'barcode' => 'nullable|string|max:100',
            ]);
            
            // Add created_by from authenticated user
            $validated['created_by'] = auth()->id();
            
            MasterItem::create($validated);
            
            return redirect()->route('master-items.index')
                ->with('success', 'Master product created successfully.');
        }
    }
    
    /**
     * Show the form for editing the specified master item.
     *
     * @param  \App\Models\MasterItem  $masterItem
     * @return \Illuminate\View\View
     */
    public function edit(MasterItem $masterItem)
    {
        return view('master-items.edit', compact('masterItem'));
    }
    
    /**
     * Update the specified master item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterItem  $masterItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MasterItem $masterItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:50|unique:master_items,sku,' . $masterItem->id,
            'barcode' => 'nullable|string|max:100',
        ]);
        
        $masterItem->update($validated);
        
        return redirect()->route('master-items.index')
            ->with('success', 'Master item updated successfully.');
    }
    
    /**
     * Remove the specified master item from storage.
     *
     * @param  \App\Models\MasterItem  $masterItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MasterItem $masterItem)
    {
        $masterItem->delete();
        
        return redirect()->route('master-items.index')
            ->with('success', 'Master item deleted successfully.');
    }
}

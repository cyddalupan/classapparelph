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
        // Always use selected_sizes array (works for single or bulk)
        $selectedSizes = $request->input('selected_sizes', []);
        
        // Validate that at least one size is selected
        if (empty($selectedSizes) || !is_array($selectedSizes)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['selected_sizes' => 'Please select at least one size.']);
        }
        
        $createdCount = 0;
        $errors = [];
        
        // Base data for all items - SKU removed (always auto-generated)
        $baseData = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
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
                
                // Function to remove vowels and keep only consonants
                $removeVowels = function($text) {
                    // Remove vowels (both uppercase and lowercase)
                    $text = preg_replace('/[aeiouAEIOU]/', '', $text);
                    // Remove spaces and special characters, keep only letters/numbers
                    $text = preg_replace('/[^A-Z0-9]/', '', $text);
                    return $text;
                };
                
                $skuParts = [];
                if (!empty($brand)) $skuParts[] = strtoupper($removeVowels($brand));
                if (!empty($type)) $skuParts[] = strtoupper($removeVowels($type));
                if (!empty($color)) $skuParts[] = strtoupper($removeVowels($color));
                
                $sku = '';
                if (!empty($skuParts)) {
                    // Generate unique SKU that won't conflict with soft-deleted items
                    $sku = implode('-', $skuParts) . '-' . $size;
                    
                    // Check if this SKU already exists (including soft-deleted)
                    $existing = MasterItem::withTrashed()->where('sku', $sku)->first();
                    if ($existing) {
                        // Add random suffix to make it unique
                        $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4);
                        $sku = $sku . '-' . $random;
                    }
                }
                
                // Create product data - ONLY fields that exist in database
                $productData = [
                    'name' => $baseData['name'],
                    'category' => $baseData['category'] ?? null,
                    'description' => $baseData['description'] ?? '',
                    'unit_price' => $baseData['unit_price'] ?? null,
                    'barcode' => $baseData['barcode'] ?? null,
                    'sku' => $sku ?: null,
                    'created_by' => $baseData['created_by'],
                ];
                
                // Add size to description if we have other details
                if (!empty($brand) || !empty($type) || !empty($color)) {
                    $sizeInfo = "Size: {$size}";
                    if (!empty($brand)) $sizeInfo .= ", Brand: {$brand}";
                    if (!empty($type)) $sizeInfo .= ", Type: {$type}";
                    if (!empty($color)) $sizeInfo .= ", Color: {$color}";
                    
                    $productData['description'] = $productData['description'] ? 
                        $productData['description'] . "\n" . $sizeInfo : $sizeInfo;
                }
                
                // Create the product - use only explicit fields
                MasterItem::create($productData);
                $createdCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Failed to create product for size {$size}: " . $e->getMessage();
            }
        }
        
        // Return success message with count
        $message = "Created {$createdCount} product" . ($createdCount !== 1 ? 's' : '') . " successfully!";
        if (!empty($errors)) {
            $message .= " (Errors: " . count($errors) . ")";
        }
        
        return redirect()->route('master-items.index')
            ->with('success', $message);
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

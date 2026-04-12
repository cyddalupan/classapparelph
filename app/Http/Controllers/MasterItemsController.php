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
            'uncategorized' => MasterItem::where('category', 'Other Products')->count(),
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
        // Get category from request
        $category = $request->input('category', '');
        
        // Only validate selected_sizes for Shirt Products
        if ($category === 'Shirt Products') {
            $selectedSizes = $request->input('selected_sizes', []);
            
            // Validate that at least one size is selected for shirts
            if (empty($selectedSizes) || !is_array($selectedSizes)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['selected_sizes' => 'Please select at least one size.']);
            }
        } else {
            // For non-shirt categories, use empty array or single item
            $selectedSizes = ['N/A'];
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
                // Function to remove vowels and keep only consonants
                $removeVowels = function($text) {
                    // Remove vowels (both uppercase and lowercase)
                    $text = preg_replace('/[aeiouAEIOU]/', '', $text);
                    // Remove spaces and special characters, keep only letters/numbers
                    $text = preg_replace('/[^A-Z0-9]/', '', $text);
                    return $text;
                };
                
                $skuParts = [];
                $descriptionParts = [];
                
                // Category-aware SKU generation
                if ($category === 'Shirt Products') {
                    $brand = $request->input('brand', '');
                    $type = $request->input('type', '');
                    $color = $request->input('color', '');
                    
                    if (!empty($brand)) $skuParts[] = strtoupper($removeVowels($brand));
                    if (!empty($type)) $skuParts[] = strtoupper($removeVowels($type));
                    if (!empty($color)) $skuParts[] = strtoupper($removeVowels($color));
                    
                    // Add to description
                    if (!empty($brand)) $descriptionParts[] = "Brand: {$brand}";
                    if (!empty($type)) $descriptionParts[] = "Type: {$type}";
                    if (!empty($color)) $descriptionParts[] = "Color: {$color}";
                    
                } else if ($category === 'Other Products') {
                    $brand = $request->input('other_brand', '');
                    $productType = $request->input('product_type', '');
                    $material = $request->input('other_material', '');
                    $color = $request->input('other_color', '');
                    
                    if (!empty($brand)) $skuParts[] = strtoupper($removeVowels($brand));
                    if (!empty($productType)) $skuParts[] = strtoupper($removeVowels($productType));
                    if (!empty($material)) $skuParts[] = strtoupper($removeVowels($material));
                    if (!empty($color)) $skuParts[] = strtoupper($removeVowels($color));
                    
                    // Add to description
                    if (!empty($brand)) $descriptionParts[] = "Brand: {$brand}";
                    if (!empty($productType)) $descriptionParts[] = "Product Type: {$productType}";
                    if (!empty($material)) $descriptionParts[] = "Material: {$material}";
                    if (!empty($color)) $descriptionParts[] = "Color: {$color}";
                    
                    // Add other fields if they exist
                    $sizeDimension = $request->input('size_dimension', '');
                    $designArea = $request->input('design_area', '');
                    $otherFeatures = $request->input('other_features', '');
                    
                    if (!empty($sizeDimension)) $descriptionParts[] = "Size/Dimensions: {$sizeDimension}";
                    if (!empty($designArea)) $descriptionParts[] = "Design/Print Area: {$designArea}";
                    if (!empty($otherFeatures)) $descriptionParts[] = "Special Features: {$otherFeatures}";
                    
                } else if ($category === 'Machine and Equipments') {
                    $brand = $request->input('machine_brand', '');
                    $machineType = $request->input('machine_type', '');
                    $specifications = $request->input('specifications', '');
                    
                    if (!empty($brand)) $skuParts[] = strtoupper($removeVowels($brand));
                    if (!empty($machineType)) $skuParts[] = strtoupper($removeVowels($machineType));
                    if (!empty($specifications)) $skuParts[] = strtoupper($removeVowels($specifications));
                    
                    // Add to description
                    if (!empty($brand)) $descriptionParts[] = "Brand: {$brand}";
                    if (!empty($machineType)) $descriptionParts[] = "Type: {$machineType}";
                    if (!empty($specifications)) $descriptionParts[] = "Specifications: {$specifications}";
                    
                } else if ($category === 'Garment Materials') {
                    $materialType = $request->input('material_type', '');
                    $materialBrand = $request->input('material_brand', '');
                    $materialColor = $request->input('material_color', '');
                    $materialSpecification = $request->input('material_specification', '');
                    $materialSku = $request->input('material_sku', '');
                    $materialName = $request->input('material_name', '');
                    
                    // Use MANUAL SKU if provided, otherwise use HYBRID system
                    if (!empty($materialSku)) {
                        $sku = $materialSku;
                    } else {
                        // HYBRID: Auto-generate from Brand + Material + Color + Item Name
                        if (!empty($materialBrand)) $skuParts[] = strtoupper($removeVowels($materialBrand));
                        if (!empty($materialType)) $skuParts[] = strtoupper($removeVowels($materialType));
                        if (!empty($materialColor)) $skuParts[] = strtoupper($removeVowels($materialColor));
                        if (!empty($materialName)) $skuParts[] = strtoupper($removeVowels($materialName));
                        
                        // Generate random suffix (ABC123 format)
                        $randomLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $randomNumbers = '0123456789';
                        
                        $randomSuffix = '';
                        // Add 3 random letters
                        for ($i = 0; $i < 3; $i++) {
                            $randomSuffix .= $randomLetters[rand(0, strlen($randomLetters) - 1)];
                        }
                        // Add 3 random numbers
                        for ($i = 0; $i < 3; $i++) {
                            $randomSuffix .= $randomNumbers[rand(0, strlen($randomNumbers) - 1)];
                        }
                        
                        // Combine: BRAND-MATERIAL-COLOR-ITEMNAME-ABC123
                        $baseSKU = implode('-', $skuParts);
                        $sku = $baseSKU ? "{$baseSKU}-{$randomSuffix}" : $randomSuffix;
                    }
                    
                    // Add to description
                    if (!empty($materialName)) $descriptionParts[] = "Item Name: {$materialName}";
                    if (!empty($materialType)) $descriptionParts[] = "Material Type: {$materialType}";
                    if (!empty($materialBrand)) $descriptionParts[] = "Brand: {$materialBrand}";
                    if (!empty($materialColor)) $descriptionParts[] = "Color: {$materialColor}";
                    if (!empty($materialSpecification)) $descriptionParts[] = "Specification: {$materialSpecification}";
                    if (!empty($materialSku)) $descriptionParts[] = "Manual SKU: {$materialSku}";
                    
                } else if ($category === 'Printing and Office Supplies') {
                    $productType = $request->input('printing_product_type', '');
                    $paperType = $request->input('paper_type', '');
                    $paperSize = $request->input('paper_size', '');
                    
                    if (!empty($productType)) $skuParts[] = strtoupper($removeVowels($productType));
                    if (!empty($paperType)) $skuParts[] = strtoupper($removeVowels($paperType));
                    if (!empty($paperSize)) $skuParts[] = strtoupper($removeVowels($paperSize));
                    
                    // Add to description
                    if (!empty($productType)) $descriptionParts[] = "Product Type: {$productType}";
                    if (!empty($paperType)) $descriptionParts[] = "Paper Type: {$paperType}";
                    if (!empty($paperSize)) $descriptionParts[] = "Paper Size: {$paperSize}";
                }
                
                $sku = '';
                if (!empty($skuParts)) {
                    // Generate unique SKU that won't conflict with soft-deleted items
                    $sku = implode('-', $skuParts);
                    if ($size !== 'N/A') {
                        $sku .= '-' . $size;
                    }
                    
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
                
                // Add category-specific details to description
                if (!empty($descriptionParts)) {
                    $details = implode(', ', $descriptionParts);
                    if ($size !== 'N/A') {
                        $details = "Size: {$size}, " . $details;
                    }
                    
                    $productData['description'] = $productData['description'] ? 
                        $productData['description'] . "\n" . $details : $details;
                } else if ($size !== 'N/A') {
                    // Just add size if no other details
                    $productData['description'] = $productData['description'] ? 
                        $productData['description'] . "\nSize: {$size}" : "Size: {$size}";
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

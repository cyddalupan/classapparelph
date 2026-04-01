<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        // Get all inventory items categorized by type
        $shirtProducts = Inventory::where('category', 'Shirt Products')->orWhere('category', 'like', '%shirt%')->latest()->get();
        $otherItems = Inventory::where('category', 'Other Items')->orWhere('category', 'like', '%other%')->latest()->get();
        $garmentMaterials = Inventory::where('category', 'Garment Materials')->orWhere('category', 'like', '%garment%')->latest()->get();
        $officeSupplies = Inventory::where('category', 'Printing and Office Supplies')->orWhere('category', 'like', '%office%')->orWhere('category', 'like', '%printing%')->latest()->get();
        $machineEquipment = Inventory::where('category', 'Machine and Equipment')->orWhere('category', 'like', '%machine%')->orWhere('category', 'like', '%equipment%')->latest()->get();
        
        // Calculate totals
        $totalProducts = Inventory::count();
        $totalValue = Inventory::sum('unit_price');
        $totalStock = Inventory::sum('current_stock');
        $categoriesCount = 5; // Fixed 5 categories
        
        return view('products.index', compact(
            'shirtProducts',
            'otherItems', 
            'garmentMaterials',
            'officeSupplies',
            'machineEquipment',
            'totalProducts',
            'totalValue',
            'totalStock',
            'categoriesCount'
        ));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $productTypes = [
            't-shirt' => 'T-Shirt',
            'hoodie' => 'Hoodie',
            'cap' => 'Cap',
            'jacket' => 'Jacket',
            'polo' => 'Polo',
            'other' => 'Other',
        ];
        
        $commonColors = [
            '#FFFFFF' => 'White',
            '#000000' => 'Black',
            '#FF0000' => 'Red',
            '#0000FF' => 'Blue',
            '#008000' => 'Green',
            '#FFFF00' => 'Yellow',
            '#FFA500' => 'Orange',
            '#800080' => 'Purple',
            '#FFC0CB' => 'Pink',
            '#A52A2A' => 'Brown',
            '#808080' => 'Gray',
            '#C0C0C0' => 'Silver',
        ];
        
        $commonSizes = [
            'XS' => 'Extra Small',
            'S' => 'Small',
            'M' => 'Medium',
            'L' => 'Large',
            'XL' => 'Extra Large',
            '2XL' => '2X Large',
            '3XL' => '3X Large',
            '4XL' => '4X Large',
        ];
        
        $commonMaterials = [
            'cotton' => '100% Cotton',
            'polyester' => 'Polyester',
            'cotton_poly' => 'Cotton-Polyester Blend',
            'fleece' => 'Fleece',
            'denim' => 'Denim',
            'canvas' => 'Canvas',
        ];
        
        $commonBrands = [
            'gildan' => 'Gildan',
            'hanes' => 'Hanes',
            'fruit_of_the_loom' => 'Fruit of the Loom',
            'champion' => 'Champion',
            'under_armour' => 'Under Armour',
            'nike' => 'Nike',
            'adidas' => 'Adidas',
            'custom' => 'Custom/No Brand',
        ];
        
        return view('products.create', compact(
            'productTypes',
            'commonColors',
            'commonSizes',
            'commonMaterials',
            'commonBrands'
        ));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:t-shirt,hoodie,cap,jacket,polo,other',
            'available_colors' => 'nullable|array',
            'available_sizes' => 'nullable|array',
            'base_price' => 'required|numeric|min:0',
            'printing_cost_per_color' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);
        
        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($path);
        }
        
        // Ensure is_active is set
        $validated['is_active'] = $request->has('is_active');
        
        // Create product
        Product::create($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $productTypes = [
            't-shirt' => 'T-Shirt',
            'hoodie' => 'Hoodie',
            'cap' => 'Cap',
            'jacket' => 'Jacket',
            'polo' => 'Polo',
            'other' => 'Other',
        ];
        
        $commonColors = [
            '#FFFFFF' => 'White',
            '#000000' => 'Black',
            '#FF0000' => 'Red',
            '#0000FF' => 'Blue',
            '#008000' => 'Green',
            '#FFFF00' => 'Yellow',
            '#FFA500' => 'Orange',
            '#800080' => 'Purple',
            '#FFC0CB' => 'Pink',
            '#A52A2A' => 'Brown',
            '#808080' => 'Gray',
            '#C0C0C0' => 'Silver',
        ];
        
        $commonSizes = [
            'XS' => 'Extra Small',
            'S' => 'Small',
            'M' => 'Medium',
            'L' => 'Large',
            'XL' => 'Extra Large',
            '2XL' => '2X Large',
            '3XL' => '3X Large',
            '4XL' => '4X Large',
        ];
        
        $commonMaterials = [
            'cotton' => '100% Cotton',
            'polyester' => 'Polyester',
            'cotton_poly' => 'Cotton-Polyester Blend',
            'fleece' => 'Fleece',
            'denim' => 'Denim',
            'canvas' => 'Canvas',
        ];
        
        $commonBrands = [
            'gildan' => 'Gildan',
            'hanes' => 'Hanes',
            'fruit_of_the_loom' => 'Fruit of the Loom',
            'champion' => 'Champion',
            'under_armour' => 'Under Armour',
            'nike' => 'Nike',
            'adidas' => 'Adidas',
            'custom' => 'Custom/No Brand',
        ];
        
        return view('products.edit', compact(
            'product',
            'productTypes',
            'commonColors',
            'commonSizes',
            'commonMaterials',
            'commonBrands'
        ));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:t-shirt,hoodie,cap,jacket,polo,other',
            'available_colors' => 'nullable|array',
            'available_sizes' => 'nullable|array',
            'base_price' => 'required|numeric|min:0',
            'printing_cost_per_color' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);
        
        // Generate slug from name if name changed
        if ($validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url) {
                $oldPath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($path);
        }
        
        // Ensure is_active is set
        $validated['is_active'] = $request->has('is_active');
        
        // Update product
        $product->update($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image_url) {
            $path = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($path);
        }
        
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
    
    /**
     * Restore a soft-deleted product.
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        
        return redirect()->route('products.index')
            ->with('success', 'Product restored successfully.');
    }
    
    /**
     * Permanently delete a product.
     */
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        
        // Delete image if exists
        if ($product->image_url) {
            $path = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($path);
        }
        
        $product->forceDelete();
        
        return redirect()->route('products.index')
            ->with('success', 'Product permanently deleted.');
    }
    
    /**
     * Show trashed products.
     */
    public function trashed()
    {
        $products = Product::onlyTrashed()->latest()->paginate(20);
        
        return view('products.trashed', compact('products'));
    }
    
    /**
     * Update stock quantity.
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);
        
        $oldQuantity = $product->stock_quantity;
        $newQuantity = $oldQuantity + $request->adjustment;
        
        if ($newQuantity < 0) {
            return back()->with('error', 'Stock cannot be negative.');
        }
        
        $product->update(['stock_quantity' => $newQuantity]);
        
        // Here you could log the stock adjustment
        
        return back()->with('success', "Stock updated from {$oldQuantity} to {$newQuantity}.");
    }
}
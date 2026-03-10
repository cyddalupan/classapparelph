<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Classic Cotton T-Shirt',
                'type' => 't-shirt',
                'description' => '100% cotton, pre-shrunk, comfortable fit for everyday wear',
                'available_colors' => json_encode(['#FFFFFF', '#000000', '#FF0000', '#0000FF', '#008000', '#FFFF00']),
                'available_sizes' => json_encode(['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
                'base_price' => 250.00,
                'printing_cost_per_color' => 50.00,
                'material' => '100% Cotton',
                'brand' => 'Gildan',
                'sku' => 'TSH-COT-001',
                'stock_quantity' => 500,
                'reorder_level' => 50,
                'image_url' => null,
                'specifications' => json_encode([
                    'weight' => '180 GSM',
                    'fit' => 'Regular Fit',
                    'sleeve' => 'Short Sleeve',
                    'neck' => 'Crew Neck',
                    'wash_care' => 'Machine wash cold, tumble dry low'
                ]),
            ],
            [
                'name' => 'Premium Polo Shirt',
                'type' => 'polo',
                'description' => 'Premium pique polo with embroidered logo option',
                'available_colors' => json_encode(['#FFFFFF', '#0000FF', '#008000', '#800080']),
                'available_sizes' => json_encode(['S', 'M', 'L', 'XL', '2XL']),
                'base_price' => 450.00,
                'printing_cost_per_color' => 75.00,
                'material' => 'Cotton/Polyester Blend',
                'brand' => 'Fruit of the Loom',
                'sku' => 'POL-PRM-001',
                'stock_quantity' => 300,
                'reorder_level' => 30,
                'image_url' => null,
                'specifications' => json_encode([
                    'weight' => '200 GSM',
                    'fit' => 'Athletic Fit',
                    'sleeve' => 'Short Sleeve',
                    'placket' => '3-button placket',
                    'collar' => 'Rib-knit collar'
                ]),
            ],
            [
                'name' => 'Hooded Sweatshirt',
                'type' => 'hoodie',
                'description' => 'Warm and comfortable hoodie for cooler weather',
                'available_colors' => json_encode(['#000000', '#808080', '#800000', '#000080']),
                'available_sizes' => json_encode(['M', 'L', 'XL', '2XL', '3XL']),
                'base_price' => 750.00,
                'printing_cost_per_color' => 100.00,
                'material' => 'Fleece',
                'brand' => 'Hanes',
                'sku' => 'HOD-FLC-001',
                'stock_quantity' => 200,
                'reorder_level' => 20,
                'image_url' => null,
                'specifications' => json_encode([
                    'weight' => '300 GSM',
                    'fit' => 'Relaxed Fit',
                    'hood' => 'Drawstring hood',
                    'pockets' => 'Kangaroo pocket',
                    'cuffs' => 'Rib-knit cuffs and waistband'
                ]),
            ],
            [
                'name' => 'Baseball Cap',
                'type' => 'cap',
                'description' => 'Adjustable baseball cap with embroidered design',
                'available_colors' => json_encode(['#000000', '#FFFFFF', '#0000FF', '#FF0000']),
                'available_sizes' => json_encode(['One Size']),
                'base_price' => 200.00,
                'printing_cost_per_color' => 150.00, // Embroidery cost
                'material' => 'Polyester',
                'brand' => 'New Era',
                'sku' => 'CAP-BBL-001',
                'stock_quantity' => 150,
                'reorder_level' => 15,
                'image_url' => null,
                'specifications' => json_encode([
                    'closure' => 'Adjustable strap',
                    'brim' => 'Curved brim',
                    'embroidery_area' => 'Front panel 10x10cm'
                ]),
            ],
            [
                'name' => 'Lightweight Jacket',
                'type' => 'jacket',
                'description' => 'Windbreaker style jacket for outdoor activities',
                'available_colors' => json_encode(['#000000', '#0000FF', '#FF0000', '#FFFF00']),
                'available_sizes' => json_encode(['S', 'M', 'L', 'XL']),
                'base_price' => 950.00,
                'printing_cost_per_color' => 125.00,
                'material' => 'Nylon/Polyester',
                'brand' => 'Champion',
                'sku' => 'JKT-LWT-001',
                'stock_quantity' => 100,
                'reorder_level' => 10,
                'image_url' => null,
                'specifications' => json_encode([
                    'weight' => 'Lightweight',
                    'water_resistance' => 'Water-resistant',
                    'closure' => 'Full-zip front',
                    'pockets' => 'Side pockets',
                    'hood' => 'Detachable hood'
                ]),
            ],
        ];

        foreach ($products as $productData) {
            $productData['slug'] = Str::slug($productData['name']);
            Product::create($productData);
        }

        $this->command->info('Product catalog seeded successfully!');
    }
}

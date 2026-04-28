<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrintingPrice;
use App\Models\PrintingComboDiscount;
use App\Models\PrintingSizeUpgrade;
use App\Models\PrintingBulkDiscount;

class PrintingPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        PrintingBulkDiscount::truncate();
        PrintingSizeUpgrade::truncate();
        PrintingComboDiscount::truncate();
        PrintingPrice::truncate();
        
        // Create print sizes
        $sizes = [
            ['name' => 'Logo', 'price' => 45.00, 'order' => 1],
            ['name' => 'Half A4', 'price' => 60.00, 'order' => 2],
            ['name' => 'A4', 'price' => 90.00, 'order' => 3],
            ['name' => 'A3', 'price' => 130.00, 'order' => 4],
            ['name' => 'A2', 'price' => 200.00, 'order' => 5],
        ];
        
        $sizeIds = [];
        foreach ($sizes as $size) {
            $printingPrice = PrintingPrice::create($size);
            $sizeIds[$size['name']] = $printingPrice->id;
        }
        
        // Create combo discounts
        $combos = [
            [
                'size1_id' => $sizeIds['Logo'],
                'size2_id' => $sizeIds['A3'],
                'discount_type' => 'fixed',
                'discount_value' => 30.00
            ],
            [
                'size1_id' => $sizeIds['Logo'],
                'size2_id' => $sizeIds['A4'],
                'discount_type' => 'fixed',
                'discount_value' => 20.00
            ],
            [
                'size1_id' => $sizeIds['Half A4'],
                'size2_id' => $sizeIds['A3'],
                'discount_type' => 'fixed',
                'discount_value' => 25.00
            ],
        ];
        
        foreach ($combos as $combo) {
            PrintingComboDiscount::create($combo);
        }
        
        // Create size upgrades
        $upgrades = [
            [
                'from_size_id' => $sizeIds['Logo'],
                'from_quantity' => 2,
                'to_size_id' => $sizeIds['Half A4'],
                'auto_apply' => true
            ],
            [
                'from_size_id' => $sizeIds['Logo'],
                'from_quantity' => 3,
                'to_size_id' => $sizeIds['A4'],
                'auto_apply' => true
            ],
            [
                'from_size_id' => $sizeIds['Half A4'],
                'from_quantity' => 2,
                'to_size_id' => $sizeIds['A3'],
                'auto_apply' => true
            ],
        ];
        
        foreach ($upgrades as $upgrade) {
            PrintingSizeUpgrade::create($upgrade);
        }
        
        // Create bulk discounts
        $bulkDiscounts = [
            ['min_garments' => 10, 'max_garments' => 24, 'discount_percent' => 5.00],
            ['min_garments' => 25, 'max_garments' => 49, 'discount_percent' => 10.00],
            ['min_garments' => 50, 'max_garments' => 9999, 'discount_percent' => 15.00],
        ];
        
        foreach ($bulkDiscounts as $bulk) {
            PrintingBulkDiscount::create($bulk);
        }
        
        $this->command->info('Printing prices, combo discounts, size upgrades, and bulk discounts seeded successfully!');
    }
}
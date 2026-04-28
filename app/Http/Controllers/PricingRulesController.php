<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintingPrice;
use App\Models\PrintingComboDiscount;
use App\Models\PrintingBulkDiscount;

class PricingRulesController extends Controller
{

    /**
     * Display pricing rules dashboard.
     */
    public function index()
    {
        // Get all services with their configuration status
        $services = [
            'printing' => [
                'name' => 'Garment Printing',
                'icon' => 'fas fa-tshirt',
                'description' => 'T-shirt, hoodie, and apparel printing',
                'configured' => PrintingPrice::count() > 0,
                'price_count' => PrintingPrice::count(),
                'combo_count' => PrintingComboDiscount::count(),
                'bulk_count' => PrintingBulkDiscount::count(),
                'edit_route' => 'printing.rules',
                'color' => 'primary',
            ],
            'bulk' => [
                'name' => 'Bulk Order Rules',
                'icon' => 'fas fa-layer-group',
                'description' => 'Quantity-based discounts for all products',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'success',
            ],
            'sublimation' => [
                'name' => 'Full Sublimation',
                'icon' => 'fas fa-paint-roller',
                'description' => 'Full garment dye sublimation printing',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'info',
            ],
            'tarpaulin' => [
                'name' => 'Tarpaulin & Banner',
                'icon' => 'fas fa-flag',
                'description' => 'Large format printing for tarps and banners',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'warning',
            ],
            'embroidery' => [
                'name' => 'Embroidery',
                'icon' => 'fas fa-thread',
                'description' => 'Thread embroidery on caps, jackets, bags',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'danger',
            ],
            'sticker' => [
                'name' => 'Sticker & Decal',
                'icon' => 'fas fa-sticky-note',
                'description' => 'Vinyl stickers, decals, and labels',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'secondary',
            ],
        ];

        return view('pricing-rules.index', compact('services'));
    }

    /**
     * Display garment printing rules.
     */
    public function printingRules()
    {
        // This redirects to the existing printing rules editor
        return redirect()->route('printing.rules');
    }

    /**
     * Display bulk order rules.
     */
    public function bulkRules()
    {
        return view('pricing-rules.bulk');
    }

    /**
     * Display sublimation rules.
     */
    public function sublimationRules()
    {
        return view('pricing-rules.sublimation');
    }

    /**
     * Display tarpaulin rules.
     */
    public function tarpaulinRules()
    {
        return view('pricing-rules.tarpaulin');
    }

    /**
     * Display embroidery rules.
     */
    public function embroideryRules()
    {
        return view('pricing-rules.embroidery');
    }

    /**
     * Display sticker rules.
     */
    public function stickerRules()
    {
        return view('pricing-rules.sticker');
    }
}
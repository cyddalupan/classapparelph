<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoriesController extends Controller
{
    /**
     * Display the inventories dashboard with dynamic category counts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Calculate dynamic category counts from database
        $categoryCounts = [
            'shirt' => Inventory::where('category', 'Shirt Products')->count(),
            'uncategorized' => Inventory::where('category', 'Uncategorized')->count(),
            'machines' => Inventory::where('category', 'Machines & Equipment')->count(),
            'materials' => Inventory::where('category', 'Garment Materials')->count(),
            'printing' => Inventory::where('category', 'Printing & Office')->count(),
        ];

        // Calculate total active items (excluding soft-deleted)
        $totalActiveItems = Inventory::count();

        // Calculate total items including soft-deleted for reference
        $totalItemsIncludingDeleted = Inventory::withTrashed()->count();

        // Pass all data to the view
        return view('inventories.index', [
            'categoryCounts' => $categoryCounts,
            'totalActiveItems' => $totalActiveItems,
            'totalItemsIncludingDeleted' => $totalItemsIncludingDeleted,
        ]);
    }
}

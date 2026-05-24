<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterItem;

class InventoriesController extends Controller
{
    /**
     * Display the inventories dashboard with dynamic category counts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Calculate dynamic category counts from master_items table
        $categoryCounts = [
            'shirt' => MasterItem::where('category', 'Shirt Products')->whereNull('deleted_at')->count(),
            'uncategorized' => MasterItem::where('category', 'Other Products')->whereNull('deleted_at')->count(),
            'machines' => MasterItem::where('category', 'Machine and Equipments')->whereNull('deleted_at')->count(),
            'materials' => MasterItem::where('category', 'Garment Materials')->whereNull('deleted_at')->count(),
            'printing' => MasterItem::where('category', 'Printing and Office Supplies')->whereNull('deleted_at')->count(),
        ];

        // Calculate total active items (excluding soft-deleted)
        $totalActiveItems = MasterItem::whereNull('deleted_at')->count();

        // Get departments for the department filter
        $departments = \DB::table('sales_departments')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get count of items assigned to iPrint (from department_master_items)
        $iprintItemCount = \DB::table('department_master_items')
            ->where('department_id', 1) // iPrint = id 1
            ->count();

        // Pass all data to the view
        return view('inventories.index', [
            'categoryCounts' => $categoryCounts,
            'totalActiveItems' => $totalActiveItems,
            'totalItemsIncludingDeleted' => MasterItem::withTrashed()->count(),
            'departments' => $departments,
            'iprintItemCount' => $iprintItemCount,
        ]);
    }
}

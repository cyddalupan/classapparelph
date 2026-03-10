<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DtfController extends Controller
{
    /**
     * Display DTF form with configurable number of rows
     */
    public function create(Request $request)
    {
        $printRows = $request->input('print_rows', 3);
        $shirtRows = $request->input('shirt_rows', 3);
        
        return view('dtf.create', [
            'printRows' => $printRows,
            'shirtRows' => $shirtRows,
        ]);
    }

    /**
     * Handle DTF form submission
     */
    public function store(Request $request)
    {
        // Check if this is an "Add More Rows" request
        if ($request->has('add_more_rows')) {
            $printRows = $request->input('print_rows_count', 3) + 2;
            $shirtRows = $request->input('shirt_rows_count', 3) + 2;
            
            return redirect()->route('dtf.create', [
                'print_rows' => $printRows,
                'shirt_rows' => $shirtRows,
            ])->withInput();
        }
        
        // Otherwise, process the actual DTF order submission
        $validated = $request->validate([
            'print_sizes' => 'required|array|min:1',
            'print_sizes.*.size' => 'required|string',
            'print_sizes.*.quantity' => 'required|integer|min:1',
            'shirt_sizes' => 'required|array|min:1',
            'shirt_sizes.*.size' => 'required|string',
            'shirt_sizes.*.quantity' => 'required|integer|min:1',
        ]);

        // Filter out empty rows
        $filteredPrintSizes = array_filter($validated['print_sizes'], function($item) {
            return !empty($item['size']) && $item['quantity'] > 0;
        });
        
        $filteredShirtSizes = array_filter($validated['shirt_sizes'], function($item) {
            return !empty($item['size']) && $item['quantity'] > 0;
        });

        // Process the DTF order here with filtered data
        // For now, just return success
        
        return redirect()->route('dtf.create')
            ->with('success', 'DTF order submitted successfully! Processed ' . 
                   count($filteredPrintSizes) . ' print sizes and ' . 
                   count($filteredShirtSizes) . ' shirt sizes.');
    }
}

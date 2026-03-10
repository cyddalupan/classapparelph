<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::where('user_id', Auth::id())
            ->orderBy('sale_date', 'desc')
            ->paginate(20);
            
        $totalSales = Sale::where('user_id', Auth::id())->sum('total_amount');
        $totalOrders = Sale::where('user_id', Auth::id())->count();
        $pendingPayments = Sale::where('user_id', Auth::id())
            ->where('payment_status', 'pending')
            ->sum('total_amount');
        $completedSales = Sale::where('user_id', Auth::id())
            ->where('sale_status', 'completed')
            ->count();
            
        return view('finance.sales', compact(
            'sales', 
            'totalSales', 
            'totalOrders', 
            'pendingPayments',
            'completedSales'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('finance.sales-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_date' => ['required', function ($attribute, $value, $fail) {
                $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s', 'Y-m-d H:i'];
                foreach ($formats as $format) {
                    $date = \DateTime::createFromFormat($format, $value);
                    if ($date && $date->format($format) === $value) {
                        return;
                    }
                }
                $fail('The date format is invalid. Please use YYYY-MM-DD format.');
            }],
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,gcash,paypal',
            'payment_status' => 'required|string|in:pending,paid,partially_paid,refunded',
            'sale_status' => 'required|string|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Format the date properly
        $saleDate = $request->sale_date;
        $date = \DateTime::createFromFormat('Y-m-d', $saleDate);
        if (!$date) {
            $date = \DateTime::createFromFormat('d/m/Y', $saleDate);
            if ($date) {
                $saleDate = $date->format('Y-m-d');
            }
        }

        // Clean up amount values
        $unitPrice = $request->unit_price;
        if (is_string($unitPrice)) {
            $unitPrice = preg_replace('/[^\d\.]/', '', $unitPrice);
            $unitPrice = (float) $unitPrice;
        }

        $sale = Sale::create([
            'user_id' => Auth::id(),
            'sale_date' => $saleDate,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'product_name' => $request->product_name,
            'product_description' => $request->product_description,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'sale_status' => $request->sale_status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('finance.sales')
            ->with('success', 'Sale added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = Sale::where('user_id', Auth::id())->findOrFail($id);
        return view('finance.sales-show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sale = Sale::where('user_id', Auth::id())->findOrFail($id);
        return view('finance.sales-edit', compact('sale'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sale = Sale::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sale_date' => ['required', function ($attribute, $value, $fail) {
                $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s', 'Y-m-d H:i'];
                foreach ($formats as $format) {
                    $date = \DateTime::createFromFormat($format, $value);
                    if ($date && $date->format($format) === $value) {
                        return;
                    }
                }
                $fail('The date format is invalid. Please use YYYY-MM-DD format.');
            }],
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,gcash,paypal',
            'payment_status' => 'required|string|in:pending,paid,partially_paid,refunded',
            'sale_status' => 'required|string|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Format the date properly
        $saleDate = $request->sale_date;
        $date = \DateTime::createFromFormat('Y-m-d', $saleDate);
        if (!$date) {
            $date = \DateTime::createFromFormat('d/m/Y', $saleDate);
            if ($date) {
                $saleDate = $date->format('Y-m-d');
            }
        }

        // Clean up amount values
        $unitPrice = $request->unit_price;
        if (is_string($unitPrice)) {
            $unitPrice = preg_replace('/[^\d\.]/', '', $unitPrice);
            $unitPrice = (float) $unitPrice;
        }

        $sale->update([
            'sale_date' => $saleDate,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'product_name' => $request->product_name,
            'product_description' => $request->product_description,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'sale_status' => $request->sale_status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('finance.sales')
            ->with('success', 'Sale updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sale = Sale::where('user_id', Auth::id())->findOrFail($id);
        $sale->delete();

        return redirect()->route('finance.sales')
            ->with('success', 'Sale deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->orderByDesc('id')->paginate(30);
        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer','items.product','payments','plans');
        return view('invoices.show', compact('invoice'));
    }

    public function debtors()
    {
        // همه فاکتورهایی که حداقل یک پرداخت (یا حتی صفر) دارند را با paid_sum می‌گیریم
        $all = Invoice::debtors()
            ->with('customer')
            ->orderByDesc('id')
            ->get();

        // فیلتر بدهکارها در PHP: مبلغ کل > مبلغ پرداخت‌شده
        $filtered = $all->filter(function ($invoice) {
            $paid = $invoice->paid_sum ?? 0;
            return $invoice->total_amount > $paid;
        });

        // اگر می‌خواهی صفحه‌بندی شود، از LengthAwarePaginator استفاده کن
        $page = request('page', 1);
        $perPage = 30;
        $items = $filtered->forPage($page, $perPage)->values();

        $invoices = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('reports.invoices.debtors', compact('invoices'));
    }


    public function history(Invoice $invoice)
    {
        $invoice->load('customer','items.product','payments.plan');
        return view('invoices.history', compact('invoice'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

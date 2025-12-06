<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\SalePurchasePreInvoice;
use App\Models\Source;
use Illuminate\Http\Request;

class PurchasePreInvoiceController extends Controller
{
    public function store(Request $request, PreInvoice $sale_pre_invoice)
    {
        // اینجا بعداً پالیسی خاص مدیر/کارشناس خرید را اضافه می‌کنیم

        $data = $request->validate([
            'source_id' => 'required|exists:sources,id',
        ]);

        // ایجاد پیش‌فاکتور خرید جدید
        $purchase = PreInvoice::create([
            'direction'    => 'purchase',
            'customer_id'  => $sale_pre_invoice->customer_id, // یا null، بسته به طراحی
            'source_id'    => $data['source_id'],
            'type'         => $sale_pre_invoice->type, // یا همیشه normal
            'status'       => 'draft',
            'total_amount' => 0,
            'formal_extra' => null,
            'created_by'   => auth()->id(),
        ]);

        // لینک بین فروش و خرید
        SalePurchasePreInvoice::create([
            'sale_pre_invoice_id'     => $sale_pre_invoice->id,
            'purchase_pre_invoice_id' => $purchase->id,
            'source_id'               => $data['source_id'],
            'created_by'              => auth()->id(),
            'status'                  => 'active',
        ]);

        return redirect()->route('pre-invoices.edit', $purchase->id)
            ->with('success','پیش‌فاکتور خرید برای این منبع ایجاد شد.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\InvoicePaymentPlan;
use App\Models\PreInvoice;
use Illuminate\Http\Request;

class PurchasePaymentPlanController extends Controller
{
    public function store(Request $request, PreInvoice $preInvoice)
    {
        abort_unless($preInvoice->direction === 'purchase', 404);

        $data = $request->validate([
            'amount'         => ['required','numeric','min:1'],
            'payment_type'   => ['required','in:cash,installment'],
            'scheduled_date' => ['required','date'],
            'note'           => ['nullable','string','max:500'],
        ]);

        $data['pre_invoice_id'] = $preInvoice->id;

        InvoicePaymentPlan::create($data);

        return back()->with('success', 'برنامه پرداخت به منبع ثبت شد.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class FinancePaymentController extends Controller
{
    public function customerPending()
    {
        $payments = Payment::with(['preInvoice.customer'])
            ->whereNotNull('pre_invoice_id')
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->paginate(30);

        return view('finance.payments.customer_pending', compact('payments'));
    }

    public function confirm(Payment $payment)
    {
        // اختیاری: محدودیت نقش مالی
        // $this->authorize('finance-confirm-payment', $payment);

        $payment->status = 'confirmed';
        $payment->finance_reject_reason = null;
        $payment->actual_paid_date = $payment->actual_paid_date ?? now();
        $payment->save();

        return back()->with('success', 'پرداخت تایید مالی شد.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'finance_reject_reason' => ['required','string','max:5000'],
        ]);

        $payment->status = 'rejected';
        $payment->finance_reject_reason = $data['finance_reject_reason'];
        $payment->save();

        return back()->with('success', 'پرداخت توسط مالی رد شد.');
    }

}

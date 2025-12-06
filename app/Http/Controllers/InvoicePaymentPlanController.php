<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoicePaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class InvoicePaymentPlanController extends Controller
{
   public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.amount'      => 'required|numeric|min:0.01',
            'items.*.payment_type'=> 'required|in:cash,installment',
            'items.*.scheduled_date' => 'required|date',
        ]);

        $sum = collect($data['items'])->sum('amount');
        if ((float) $sum !== (float) $invoice->total_amount) {
            return response()->json([
                'ok' => false,
                'message' => 'جمع اقساط باید دقیقاً برابر مبلغ فاکتور باشد.'
            ], 422);
        }

        // پاک کردن برنامه قبلی و ثبت جدید
        $invoice->plans()->delete();

        foreach ($data['items'] as $item) {
            $invoice->plans()->create($item);
        }

        return response()->json(['ok' => true]);
    }

    public function due(Request $request)
    {
        $from = $request->input('from', now()->toDateString());
        $to   = $request->input('to',   now()->addWeek()->toDateString());

        $plans = InvoicePaymentPlan::dueBetween($from,$to)->orderBy('scheduled_date')->get();

        return view('reports.plans.due', compact('plans','from','to'));
    }

}

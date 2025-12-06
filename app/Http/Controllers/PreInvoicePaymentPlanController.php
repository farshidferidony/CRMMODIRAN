<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use Illuminate\Http\Request;

class PreInvoicePaymentPlanController extends Controller
{
    // public function store(Request $request, PreInvoice $pre_invoice)
    // {
    //     $data = $request->validate([
    //         'items'               => ['required','array','min:1'],
    //         'items.*.amount'      => ['required','numeric','min:0.01'],
    //         'items.*.scheduled_date' => ['required','date'],
    //         'items.*.payment_type'   => ['required','in:cash,installment'],
    //     ]);

    //     $sum = collect($data['items'])->sum('amount');
    //     if ((float)$sum !== (float)$pre_invoice->total_amount) {
    //         return response()->json([
    //             'ok'      => false,
    //             'message' => 'جمع اقساط با مبلغ پیش‌فاکتور برابر نیست.',
    //         ], 422);
    //     }

    //     // اگر می‌خواهی قبلی‌ها را پاک کنی
    //     $pre_invoice->plans()->delete();

    //     foreach ($data['items'] as $item) {
    //         $pre_invoice->plans()->create([
    //             'amount'         => $item['amount'],
    //             'scheduled_date' => $item['scheduled_date'],
    //             'payment_type'   => $item['payment_type'],
    //             // سایر فیلدهای plan اگر داری (مثل note)...
    //         ]);
    //     }

    //     return response()->json([
    //         'ok'      => true,
    //         'message' => 'برنامه پرداخت ثبت شد.',
    //     ]);
    // }

    public function store(Request $request, PreInvoice $pre_invoice)
    {
        $data = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.amount'      => 'required|numeric|min:0.01',
            'items.*.payment_type'=> 'required|in:cash,installment',
            'items.*.scheduled_date' => 'required|date',
        ]);

        $sum = collect($data['items'])->sum('amount');
        if ((float) $sum !== (float) $pre_invoice->total_amount) {
            return response()->json([
                'ok' => false,
                'message' => 'جمع اقساط باید دقیقاً برابر مبلغ فاکتور باشد.'
            ], 422);
        }

        // پاک کردن برنامه قبلی و ثبت جدید
        $pre_invoice->plans()->delete();

        foreach ($data['items'] as $item) {
            $pre_invoice->plans()->create($item);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'برنامه پرداخت ثبت شد.',
        ]);
    }

}

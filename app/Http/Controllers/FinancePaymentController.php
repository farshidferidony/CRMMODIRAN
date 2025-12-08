<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PreInvoice;
use App\Enums\PreInvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

    // public function confirm(Payment $payment)
    // {
    //     // اختیاری: محدودیت نقش مالی
    //     // $this->authorize('finance-confirm-payment', $payment);

    //     $payment->status = 'confirmed';
    //     $payment->finance_reject_reason = null;
    //     $payment->actual_paid_date = $payment->actual_paid_date ?? now();
    //     $payment->save();

    //     return back()->with('success', 'پرداخت تایید مالی شد.');
    // }

    // public function confirm(Payment $payment, Request $request)
    // {
    //     DB::transaction(function () use ($payment, $request) {

    //         // ۱) منطق قبلی تأیید مالی پرداخت
    //         $payment->status = 'confirmed';
    //         $payment->finance_reject_reason = null;
    //         $payment->actual_paid_date = $payment->actual_paid_date ?? now();
    //         $payment->paid_date        = $payment->paid_date ?? $payment->actual_paid_date;

    //         // اگر مالی بخواهد مبلغ را اصلاح کند (اختیاری)
    //         if ($request->filled('paid_amount')) {
    //             $payment->paid_amount = $request->input('paid_amount');
    //         }

    //         if ($request->filled('actual_paid_date')) {
    //             $payment->actual_paid_date = $request->input('actual_paid_date');
    //             $payment->paid_date        = $request->input('actual_paid_date');
    //         }

    //         $payment->save();

    //         // ۲) اگر این پرداخت برای پیش‌فاکتور است، وضعیت پیش‌فاکتور را جلو ببر
    //         if ($payment->pre_invoice_id) {
    //             /** @var PreInvoice $preInvoice */
    //             $preInvoice = PreInvoice::find($payment->pre_invoice_id);

    //             if ($preInvoice) {
    //                 // مجموع پرداخت‌های تایید شده پیش‌فاکتور
    //                 $confirmedSum = $preInvoice->payments()
    //                     ->where('status', 'confirmed')
    //                     ->sum(DB::raw('COALESCE(paid_amount, amount)'));

    //                 // حد لازم پیش‌پرداخت (اگر فیلد جداگانه داری)
    //                 $requiredAdvance = (float) ($preInvoice->required_advance_amount ?? 0);

    //                 // اگر فیلد تعیین نشده، همین که هر مبلغی تایید شد، کافی است
    //                 if ($requiredAdvance === 0 || $confirmedSum >= $requiredAdvance) {
    //                     $preInvoice->status = PreInvoiceStatus::AdvanceFinanceApproved;
    //                     $preInvoice->save();
    //                 }
    //             }
    //         }

    //         // ۳) اگر payment برای فاکتور عادی است (invoice_id != null)،
    //         //    همین‌جا می‌توانی منطق قبلی‌ات برای آپدیت وضعیت فاکتور را هم اضافه کنی.
    //     });

    //     return back()->with('success', 'پرداخت توسط واحد مالی تایید شد.');
    // }


    public function confirm(Payment $payment, Request $request)
    {
        DB::transaction(function () use ($payment, $request) {

            // ۱) منطق قبلی تأیید مالی پرداخت
            $payment->status = 'confirmed';
            $payment->finance_reject_reason = null;
            $payment->actual_paid_date = $payment->actual_paid_date ?? now();
            $payment->paid_date        = $payment->paid_date ?? $payment->actual_paid_date;

            if ($request->filled('paid_amount')) {
                $payment->paid_amount = $request->input('paid_amount');
            }

            if ($request->filled('actual_paid_date')) {
                $payment->actual_paid_date = $request->input('actual_paid_date');
                $payment->paid_date        = $request->input('actual_paid_date');
            }

            $payment->save();

            // ۲) اگر این پرداخت برای پیش‌فاکتور است
            if ($payment->pre_invoice_id) {
                /** @var PreInvoice|null $preInvoice */
                $preInvoice = PreInvoice::find($payment->pre_invoice_id);

                if ($preInvoice) {

                    // مجموع پرداخت‌های تایید شده
                    $confirmedSum = $preInvoice->payments()
                        ->where('status', 'confirmed')
                        ->sum(DB::raw('COALESCE(paid_amount, amount)'));

                    $requiredAdvance = (float) ($preInvoice->required_advance_amount ?? 0);

                    // اگر فروش است: منطق فعلی‌ات
                    if ($preInvoice->direction === 'sales') {

                        if ($requiredAdvance === 0 || $confirmedSum >= $requiredAdvance) {
                            $preInvoice->status = PreInvoiceStatus::AdvanceFinanceApproved;
                            $preInvoice->save();
                        }

                    }

                    // اگر خرید است: تایید پرداخت/پیش‌پرداخت به منبع
                    if ($preInvoice->direction === 'purchase') {

                        // اگر required_advance برای خرید هم استفاده می‌کنی،
                        // همین شرط را استفاده کن، وگرنه صرفاً وجود یک پرداخت تایید‌شده کافی است.
                        if ($requiredAdvance === 0 || $confirmedSum >= $requiredAdvance) {

                            // ۱) فلگ مخصوص پیش‌فاکتور خرید
                            $preInvoice->supplier_payment_approved = true;
                            $preInvoice->save();

                            // ۲) اینجا هنوز خرید آیتم‌ها را نهایی نمی‌کنیم؛
                            // فقط اجازه می‌دهیم کارشناس خرید در صفحه purchase_show
                            // وزن نهایی را وارد و "finalize" کند.
                        }
                    }
                }
            }

            // ۳) اگر payment برای فاکتور عادی است (invoice_id != null)،
            //    منطق قبلی وضعیت فاکتور را هم اینجا بگذار.
        });

        return back()->with('success', 'پرداخت توسط واحد مالی تایید شد.');
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

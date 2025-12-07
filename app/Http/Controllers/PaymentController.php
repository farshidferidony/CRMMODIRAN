<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoicePaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request, Invoice $invoice)
    {
        $remaining = $invoice->remaining_amount;

        $data = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_type'     => 'required|in:cash,installment',
            'scheduled_date'   => 'required|date',
            'is_paid'          => 'required|in:0,1',
            'actual_paid_date' => 'nullable|date',
            'receipt'          => 'nullable|file|max:5120',
        ]);


        if ($data['amount'] > $remaining) {
            return back()->withErrors(['amount' => 'مبلغ وارد شده بیشتر از مانده فاکتور است.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts','public');
        }

        $status = $data['is_paid'] ? 'confirmed' : 'pending';

        $paidDate = $data['is_paid']
        ? ($data['actual_paid_date'] ?? now()->toDateString())
        : null;


        $payment = Payment::create([
            'invoice_id'       => $invoice->id,
            'pre_invoice_id'   => null,
            'customer_id'      => $invoice->customer_id,
            'amount'           => $data['amount'],
            'payment_type'     => $data['payment_type'],
            'paid_date'        => $paidDate,
            'scheduled_date'   => $data['scheduled_date'],
            'actual_paid_date' => $data['actual_paid_date'],
            'receipt_path'     => $path,
            'status'           => $status,
        ]);

        // به‌روزرسانی وضعیت فاکتور اگر کل مانده تسویه شد
        $paidSum = $invoice->payments()->where('status','confirmed')->sum('amount');
        if ($paidSum >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }

        return back()->with('success','برنامه/پرداخت با موفقیت ثبت شد.');
    }

    

    public function markPaid(Request $request, Payment $payment)
    {
        $invoice = $payment->invoice;

        $data = $request->validate([
            'paid_amount'      => 'required|numeric|min:0.01',
            'actual_paid_date' => 'required|date',
            'receipt'          => 'nullable|file|max:5120',
        ]);

        // حداکثر مجاز برای این پرداخت = مانده فاکتور
        $remaining = $invoice->remaining_amount;
        if ($data['paid_amount'] > $remaining) {
            return back()->withErrors(['paid_amount' => 'مبلغ واقعی بیشتر از بدهی باقی‌مانده است.'])->withInput();
        }

        $path = $payment->receipt_path;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts','public');
        }

        $payment->update([
            'paid_amount'      => $data['paid_amount'],
            'status'           => 'confirmed',
            'paid_date'        => $data['actual_paid_date'],
            'actual_paid_date' => $data['actual_paid_date'],
            'receipt_path'     => $path,
        ]);

        // آپدیت وضعیت فاکتور
        $paidSum = $invoice->payments()
            ->where('status','confirmed')
            ->sum(DB::raw('COALESCE(paid_amount, amount)'));

        if ($paidSum >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }

        return back()->with('success','پرداخت واقعی ثبت شد.');
    }

    
    
    public function payPlan(Request $request, InvoicePaymentPlan $plan)
    {
        $invoice = $plan->invoice;

        // اگر این قسط قبلاً کامل تسویه شده، جلوی دوباره‌کاری را بگیر
        if ($plan->is_completed) {
            return back()->with('error','این قسط قبلاً به طور کامل تسویه شده است.');
        }

        $data = $request->validate([
            'paid_amount'      => 'required|numeric|min:0.01',
            'actual_paid_date' => 'required|date',
            'receipt'          => 'nullable|file|max:5120',
        ]);

        $planned = $plan->amount;          // مبلغی که در برنامه برای این قسط ثبت شده (مثلاً 1000)
        $paid    = $data['paid_amount'];   // مبلغی که الان مشتری واقعاً پرداخت کرده (مثلاً 500)

        // فیش
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts','public');
        }

        // ۱) همیشه یک رکورد Payment برای این پرداخت می‌سازیم
        Payment::create([
            'invoice_id'       => $invoice->id,
            'pre_invoice_id'   => null,
            'customer_id'      => $invoice->customer_id,
            'plan_id'          => $plan->id,
            'amount'           => $planned,                 // مبلغ برنامه‌ای این قسط
            'paid_amount'      => min($paid, $planned),     // مبلغی که الان واقعاً پرداخت شد
            'payment_type'     => $plan->payment_type,
            'scheduled_date'   => $plan->scheduled_date,
            'actual_paid_date' => $data['actual_paid_date'],
            'paid_date'        => $data['actual_paid_date'],
            'status'           => 'pending',
            'receipt_path'     => $path,
        ]);

        if ($paid >= $planned) {
            // ۲-الف) قسط به طور کامل یا بیشتر از مقدارش پرداخت شده
            $plan->update([
                'is_completed' => true,
                'note'         => 'قسط به طور کامل پرداخت شد',
            ]);
        } else {
            // ۲-ب) پرداخت کمتر از مبلغ قسط (سناریوی تو: 1000 برنامه، 500 پرداخت شده)
            $remaining = $planned - $paid; // 500 باقیمانده

            // این قسط اولیه را کامل‌شده علامت بزن که دیگر روی آن پرداخت ثبت نشود
            $plan->update([
                'is_completed' => true,
                'note'         => 'این قسط با پرداخت جزئی بسته شد',
            ]);

            // ۲-ب-۱) قسط جدید برای مانده قسط قبلی
            InvoicePaymentPlan::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $remaining,                       // 500 باقی‌مانده
                'payment_type'   => $plan->payment_type,
                'scheduled_date' => $plan->scheduled_date,           // همان تاریخ برنامه‌ریزی‌شده قبلی
                'is_completed'   => false,
                'note'           => 'باقی‌مانده از قسط قبلی (#'.$plan->id.')',
            ]);
        }

        // ۳) آپدیت وضعیت فاکتور بر اساس مجموع paid_amount تأییدشده
        $paidSum = $invoice->payments()
            ->where('status','confirmed')
            ->sum(DB::raw('paid_amount'));

        if ($paidSum >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }

        return back()->with('success','پرداخت برای این قسط ثبت شد.');
    }

    public function prePayPlan(Request $request, InvoicePaymentPlan $plan)
    {
        $invoice = $plan->preInvoice;

        // اگر این قسط قبلاً کامل تسویه شده، جلوی دوباره‌کاری را بگیر
        if ($plan->is_completed) {
            return back()->with('error','این قسط قبلاً به طور کامل تسویه شده است.');
        }

        $data = $request->validate([
            'paid_amount'      => 'required|numeric|min:0.01',
            'actual_paid_date' => 'required|date',
            'receipt'          => 'nullable|file|max:5120',
        ]);

        $planned = $plan->amount;          // مبلغی که در برنامه برای این قسط ثبت شده (مثلاً 1000)
        $paid    = $data['paid_amount'];   // مبلغی که الان مشتری واقعاً پرداخت کرده (مثلاً 500)

        // فیش
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts','public');
        }

        // ۱) همیشه یک رکورد Payment برای این پرداخت می‌سازیم
        Payment::create([
            'invoice_id'       => null,
            'pre_invoice_id'   => $invoice->id,
            'customer_id'      => $invoice->customer_id,
            'plan_id'          => $plan->id,
            'amount'           => $planned,                 // مبلغ برنامه‌ای این قسط
            'paid_amount'      => min($paid, $planned),     // مبلغی که الان واقعاً پرداخت شد
            'payment_type'     => $plan->payment_type,
            'scheduled_date'   => $plan->scheduled_date,
            'actual_paid_date' => $data['actual_paid_date'],
            'paid_date'        => $data['actual_paid_date'],
            'status'           => 'pending',
            'receipt_path'     => $path,
        ]);

        if ($paid >= $planned) {
            // ۲-الف) قسط به طور کامل یا بیشتر از مقدارش پرداخت شده
            $plan->update([
                'is_completed' => true,
                'note'         => 'قسط به طور کامل پرداخت شد',
            ]);
        } else {
            // ۲-ب) پرداخت کمتر از مبلغ قسط (سناریوی تو: 1000 برنامه، 500 پرداخت شده)
            $remaining = $planned - $paid; // 500 باقیمانده

            // این قسط اولیه را کامل‌شده علامت بزن که دیگر روی آن پرداخت ثبت نشود
            $plan->update([
                'is_completed' => true,
                'note'         => 'این قسط با پرداخت جزئی بسته شد',
            ]);

            // ۲-ب-۱) قسط جدید برای مانده قسط قبلی
            InvoicePaymentPlan::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $remaining,                       // 500 باقی‌مانده
                'payment_type'   => $plan->payment_type,
                'scheduled_date' => $plan->scheduled_date,           // همان تاریخ برنامه‌ریزی‌شده قبلی
                'is_completed'   => false,
                'note'           => 'باقی‌مانده از قسط قبلی (#'.$plan->id.')',
            ]);
        }

        // ۳) آپدیت وضعیت فاکتور بر اساس مجموع paid_amount تأییدشده
        $paidSum = $invoice->payments()
            ->where('status','confirmed')
            ->sum(DB::raw('paid_amount'));

        if ($paidSum >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }

        return back()->with('success','پرداخت برای این قسط ثبت شد.');
    }

    public function storeCustomer(Request $request, PreInvoice $pre_invoice)
    {
        // $this->authorize('record-customer-payment', $pre_invoice);

        $data = $request->validate([
            'amount'    => ['required','numeric','min:0.01'],
            'type'      => ['required','in:full,prepayment,installment'],
            'method'    => ['nullable','string','max:255'],
            'reference' => ['nullable','string','max:255'],
            'paid_at'   => ['nullable','date'],
        ]);

        $data['direction'] = 'customer';
        $data['pre_invoice_id'] = $pre_invoice->id;
        $data['recorded_by'] = auth()->id();

        Payment::create($data);

        return back()->with('success', 'پرداخت مشتری ثبت شد و منتظر تایید مالی است.');
    }


    // public function payPlan(Request $request, InvoicePaymentPlan $plan)
    // {
    //     $invoice = $plan->invoice;

    //     $data = $request->validate([
    //         'paid_amount'      => 'required|numeric|min:0.01',
    //         'actual_paid_date' => 'required|date',
    //         'receipt'          => 'nullable|file|max:5120',
    //     ]);

    //     if ($plan->is_completed) {
    //         return back()->with('error','این قسط قبلاً تسویه شده است.');
    //     }

    //     $planned = $plan->amount;          // مثلاً 100
    //     $paid    = $data['paid_amount'];   // مثلاً 50

    //     // آپلود فیش
    //     $path = null;
    //     if ($request->hasFile('receipt')) {
    //         $path = $request->file('receipt')->store('receipts','public');
    //     }

    //     // ۱) همیشه یک ردیف Payment ثبت می‌کنیم (پرداخت انجام‌شده)
    //     Payment::create([
    //         'invoice_id'       => $invoice->id,
    //         'pre_invoice_id'   => null,
    //         'customer_id'      => $invoice->customer_id,
    //         'plan_id'          => $plan->id,
    //         'amount'           => $planned,                 // مبلغی که قرار بوده بپردازد
    //         'paid_amount'      => min($paid, $planned),     // مبلغ واقعی این پرداخت
    //         'payment_type'     => $plan->payment_type,
    //         'scheduled_date'   => $plan->scheduled_date,
    //         'actual_paid_date' => $data['actual_paid_date'],
    //         'paid_date'        => $data['actual_paid_date'],
    //         'status'           => 'confirmed',
    //         'receipt_path'     => $path,
    //     ]);

    //     if ($paid >= $planned) {
    //         // ۲-الف) پرداخت به اندازه قسط یا بیشتر: این قسط کامل تسویه شد
    //         $plan->update([
    //             'is_completed' => true,
    //         ]);
    //     } else {
    //         // ۲-ب) پرداخت کمتر از قسط: باید یک قسط جدید برای مانده بسازیم
    //         $remaining = $planned - $paid; // مثلا 50

    //         // این قسط اولیه را کامل شده علامت بزن (که دیگر دستکاری نشود)
    //         $plan->update([
    //             'is_completed' => true,
    //         ]);

    //         // قسط جدید با عنوان «مانده قسط قبلی»
    //         InvoicePaymentPlan::create([
    //             'invoice_id'     => $invoice->id,
    //             'amount'         => $remaining,
    //             'payment_type'   => $plan->payment_type,
    //             'scheduled_date' => $plan->scheduled_date,
    //             'is_completed'   => false,
    //             'note'           => 'مانده قسط قبلی (قسط #' . $plan->id . ')',
    //         ]);

    //         // InvoicePaymentPlan::create([
    //         //     'invoice_id'     => $invoice->id,
    //         //     'amount'         => $remaining,
    //         //     'payment_type'   => $plan->payment_type,
    //         //     'scheduled_date' => $plan->scheduled_date, // همان تاریخ
    //         //     'is_completed'   => false,
    //         // ]);
    //     }

    //     // آپدیت وضعیت فاکتور براساس مجموع پرداخت‌های تاییدشده
    //     $paidSum = $invoice->payments()
    //         ->where('status','confirmed')
    //         ->sum(DB::raw('paid_amount'));

    //     if ($paidSum >= $invoice->total_amount) {
    //         $invoice->status = 'paid';
    //         $invoice->save();
    //     }

    //     return back()->with('success','پرداخت برای این قسط ثبت شد.');
    // }



    // public function payPlan(Request $request, InvoicePaymentPlan $plan)
    // {
    //     $invoice = $plan->invoice;

    //     $data = $request->validate([
    //         'paid_amount'      => 'required|numeric|min:0.01',
    //         'actual_paid_date' => 'required|date',
    //         'receipt'          => 'nullable|file|max:5120',
    //     ]);

    //     $planned = $plan->amount; // مثلاً 100,000,000
    //     $paid    = $data['paid_amount']; // مثلاً 50,000,000

    //     // فایل
    //     $path = null;
    //     if ($request->hasFile('receipt')) {
    //         $path = $request->file('receipt')->store('receipts','public');
    //     }

    //     if ($paid >= $planned) {
    //         // پرداخت کامل یا بیشتر از قسط
    //         Payment::create([
    //             'invoice_id'       => $invoice->id,
    //             'customer_id'      => $invoice->customer_id,
    //             'plan_id'          => $plan->id,
    //             'amount'           => $planned,
    //             'paid_amount'      => $planned,
    //             'payment_type'     => 'cash', // یا از خود plan
    //             'scheduled_date'   => $plan->scheduled_date,
    //             'actual_paid_date' => $data['actual_paid_date'],
    //             'paid_date'        => $data['actual_paid_date'],
    //             'status'           => 'confirmed',
    //             'receipt_path'     => $path,
    //         ]);
    //         // این قسط به‌طور کامل تسویه شد؛ می‌توانی فلگ is_completed روی Plan بگذاری
    //     } else {
    //         // پرداخت کمتر از برنامه؛ مثلاً 50 از 100
    //         $remaining = $planned - $paid;

    //         // ۱) پرداخت انجام‌شده را ثبت کن
    //         Payment::create([
    //             'invoice_id'       => $invoice->id,
    //             'customer_id'      => $invoice->customer_id,
    //             'plan_id'          => $plan->id,
    //             'amount'           => $paid, // بخش پرداخت‌شده
    //             'paid_amount'      => $paid,
    //             'payment_type'     => 'cash', // یا از Plan
    //             'scheduled_date'   => $plan->scheduled_date,
    //             'actual_paid_date' => $data['actual_paid_date'],
    //             'paid_date'        => $data['actual_paid_date'],
    //             'status'           => 'confirmed',
    //             'receipt_path'     => $path,
    //         ]);

    //         // ۲) خود برنامه را به اندازه مبلغ باقی‌مانده کوچک کن
    //         $plan->update([
    //             'amount' => $remaining, // حالا این Plan یعنی قسط باقیمانده 50 میلیونی
    //         ]);

    //         // اگر ترجیح می‌دهی یک Plan جدید بسازی، آنجا هم به‌جای update، create جدید کن.
    //     }

    //     // به‌روزرسانی وضعیت فاکتور بر اساس مجموع paid_amount تاییدشده
    //     $paidSum = $invoice->payments()
    //         ->where('status','confirmed')
    //         ->sum(DB::raw('paid_amount'));

    //     if ($paidSum >= $invoice->total_amount) {
    //         $invoice->status = 'paid';
    //         $invoice->save();
    //     }

    //     return response()->json(['ok' => true]);
    // }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
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


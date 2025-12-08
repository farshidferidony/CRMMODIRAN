<?php

namespace App\Http\Controllers;


use App\Models\PreInvoice;
use App\Models\Transport;
use App\Enums\PreInvoiceStatus;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function index(PreInvoice $preInvoice)
    {
        // فقط روی پیش‌فاکتور فروش و بعد از ShippingRequested
        // $this->authorize('viewTransports', $preInvoice);

        $preInvoice->load('transports');

        return view('transports.index', compact('preInvoice'));
    }

    public function store(Request $request, PreInvoice $preInvoice)
    {
        // کارشناس فروش که روی این پیش‌فاکتور کار می‌کند
        // $this->authorize('requestShipping', $preInvoice);

        // فقط اگر قبلا به مرحله ShippingRequested رسیده
        if ($preInvoice->status !== PreInvoiceStatus::ShippingRequested) {
            return back()->with('error', 'امکان ایجاد فرم حمل در وضعیت فعلی پیش‌فاکتور وجود ندارد.');
        }

        $transport = Transport::create([
            'pre_invoice_id' => $preInvoice->id,
            'status'         => 'requested_by_sales',
        ]);

        // هدایت به صفحه تکمیل فرم حمل توسط کارشناس فروش
        return redirect()
            ->route('transports.edit', $transport)
            ->with('success', 'فرم حمل جدید برای این پیش‌فاکتور ایجاد شد.');
    }

    // public function edit(Transport $transport)
    // {
    //     $transport->load('preInvoice');

    //     // بسته به نقش کاربر، بخش‌های مختلف فرم را نشان می‌دهی
    //     // $this->authorize('edit', $transport);

    //     return view('transports.edit', compact('transport'));
    // }

    public function edit(Transport $transport)
    {
        $transport->load(['preInvoice.customer']);

        $customer = $transport->preInvoice?->customer;
        if ($customer) {
            // مثال: اگر type = person/ company می‌توانی لاجیک بیشتری بگذاری
            $fullName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));

            $transport->receiver_company         = $transport->receiver_company         ?? optional($customer->company)->name;
            $transport->receiver_name            = $transport->receiver_name            ?? $fullName;
            $transport->receiver_postal_code     = $transport->receiver_postal_code     ?? $customer->postal_code;
            $transport->receiver_national_code   = $transport->receiver_national_code   ?? $customer->national_code;
            $transport->receiver_phone           = $transport->receiver_phone           ?? $customer->phone;
            $transport->receiver_mobile          = $transport->receiver_mobile          ?? $customer->mobile;
            $transport->receiver_activity_address= $transport->receiver_activity_address?? $customer->address;
        }


        return view('transports.edit', compact('transport'));
    }




    public function update(Request $request, Transport $transport)
    {
        // $this->authorize('edit', $transport);

        // ولیدیشن فقط مرحله کارشناس فروش (ستاره‌دارها)
        $data = $request->validate([
            // بخش اول
            'unloading_confirmed'      => ['required', 'boolean'],
            'shipping_type'            => ['required', 'in:inner_city,outer_city'],
            'transfer_type'            => ['required', 'in:single_stage,two_stage'],
            'requested_truck_type'     => ['required', 'in:lowboy,flat_trailer,roll_trailer,side_trailer,ten_wheeler,single,truck_911,khaawar,khaawar_steel,nissan,nissan_steel,pickup,bunker'],
            'requested_wagon_type'     => ['nullable', 'in:normal,russian'],

            // بخش دوم: فرستنده
            'sender_name'              => ['required', 'string', 'max:255'],
            'sender_postal_code'       => ['required', 'string', 'max:20'],
            'sender_national_code'     => ['required', 'string', 'max:20'],
            'sender_phone'             => ['required', 'string', 'max:30'],

            // بخش سوم: گیرنده و محل تخلیه
            'receiver_company'         => ['required', 'string', 'max:255'],
            'receiver_name'            => ['required', 'string', 'max:255'],
            'receiver_postal_code'     => ['required', 'string', 'max:20'],
            'receiver_national_code'   => ['required', 'string', 'max:20'],
            'receiver_mobile'          => ['required', 'string', 'max:30'],
            'receiver_phone'           => ['nullable', 'string', 'max:30'],
            'receiver_activity_address'=> ['required', 'string'],

            'unloading_place_approved' => ['required', 'boolean'],
            'unloading_address'        => ['nullable', 'string'],
            'unloading_postal_code'    => ['nullable', 'string', 'max:20'],
            'unloading_responsible'    => ['nullable', 'string', 'max:255'],
            'unloading_responsible_phone' => ['nullable', 'string', 'max:30'],

            'approve_sales_expert'     => ['required', 'boolean'],
        ]);

        // اگر دو مرحله‌ای است، نوع واگن الزامی شود
        if ($data['transfer_type'] === 'two_stage' && empty($data['requested_wagon_type'])) {
            return back()->withErrors(['requested_wagon_type' => 'انتخاب نوع واگن الزامی است.'])->withInput();
        }

        // اگر محل تخلیه مورد تایید نیست، فیلدهای مربوطه الزامی شوند
        if (! $data['unloading_place_approved']) {
            $request->validate([
                'unloading_address'          => ['required', 'string'],
                'unloading_postal_code'      => ['required', 'string', 'max:20'],
                'unloading_responsible'      => ['required', 'string', 'max:255'],
                'unloading_responsible_phone'=> ['required', 'string', 'max:30'],
            ]);
        }

        $transport->fill([
            'unloading_confirmed'          => $data['unloading_confirmed'],
            'shipping_type'                => $data['shipping_type'],
            'transfer_type'                => $data['transfer_type'],
            'requested_truck_type'         => $data['requested_truck_type'],
            'requested_wagon_type'         => $data['requested_wagon_type'] ?? null,

            'sender_name'                  => $data['sender_name'],
            'sender_postal_code'           => $data['sender_postal_code'],
            'sender_national_code'         => $data['sender_national_code'],
            'sender_phone'                 => $data['sender_phone'],

            'receiver_company'             => $data['receiver_company'],
            'receiver_name'                => $data['receiver_name'],
            'receiver_postal_code'         => $data['receiver_postal_code'],
            'receiver_national_code'       => $data['receiver_national_code'],
            'receiver_phone'               => $data['receiver_phone'] ?? null,
            'receiver_mobile'              => $data['receiver_mobile'],
            'receiver_activity_address'    => $data['receiver_activity_address'],

            'unloading_place_approved'     => $data['unloading_place_approved'],
            'unloading_address'            => $data['unloading_place_approved'] ? null : $data['unloading_address'],
            'unloading_postal_code'        => $data['unloading_place_approved'] ? null : $data['unloading_postal_code'],
            'unloading_responsible'        => $data['unloading_place_approved'] ? null : $data['unloading_responsible'],
            'unloading_responsible_phone'  => $data['unloading_place_approved'] ? null : $data['unloading_responsible_phone'],

            'approved_by_sales_expert'     => $data['approve_sales_expert'],
        ]);

        // اگر کارشناس فروش تایید کرد، وضعیت فرم حمل را یک گام جلو ببریم
        if ($data['approve_sales_expert']) {
            $transport->status = 'completed_by_sales';
        }

        $transport->save();

        return redirect()
            ->route('pre_invoices.transports.index', $transport->pre_invoice_id)
            ->with('success', 'فرم حمل توسط کارشناس فروش ثبت و تایید شد.');
    }
}

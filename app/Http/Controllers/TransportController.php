<?php

namespace App\Http\Controllers;


use App\Models\PreInvoice;
use App\Models\Transport;
use App\Enums\PreInvoiceStatus;
use Illuminate\Http\Request;

use App\Enums\TransportStatus;


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

        // $transport = Transport::create([
        //     'pre_invoice_id' => $preInvoice->id,
        //     'status'         => 'requested_by_sales',
        // ]);

        $transport = Transport::create([
            'pre_invoice_id' => $preInvoice->id,
            'status'         => TransportStatus::RequestedBySales,
        ]);


        // هدایت به صفحه تکمیل فرم حمل توسط کارشناس فروش
        return redirect()
            ->route('transports.edit', $transport)
            ->with('success', 'فرم حمل جدید برای این پیش‌فاکتور ایجاد شد.');
    }

    public function edit(Transport $transport)
    {
        
        if ($transport->status instanceof TransportStatus) {
            if ($transport->status !== TransportStatus::RequestedBySales) {
                return redirect()
                    ->route('pre_invoices.transports.index', $transport->pre_invoice_id)
                    ->with('error', 'این فرم حمل از مرحله کارشناس فروش عبور کرده و در حال حاضر از این بخش قابل ویرایش نیست.');
            }
        }

        $transport->load([
            'preInvoice.customer.persons.addresses.country',
            'preInvoice.customer.persons.addresses.province',
            'preInvoice.customer.persons.addresses.city',
            'preInvoice.customer.persons.addresses.contacts',
            'preInvoice.customer.companies.addresses.country',
            'preInvoice.customer.companies.addresses.province',
            'preInvoice.customer.companies.addresses.city',
            'preInvoice.customer.companies.addresses.contacts',
        ]);

        $customer = $transport->preInvoice?->customer;

        $customerScope = $customer?->customer_scope; // domestic | foreign | null
        $person        = $customer?->primaryPerson();
        $company       = $customer?->primaryCompany();
        $isPerson      = (bool) $person && ! $company;
        $isCompany     = (bool) $company;

        // انتخاب آدرس ترجیحی: اول شرکت، اگر نبود شخص
        $addr = null;
        if ($company && $company->addresses->isNotEmpty()) {
            $addr = $company->addresses->first();
        } elseif ($person && $person->addresses->isNotEmpty()) {
            $addr = $person->addresses->first();
        }

        // استخراج موبایل از contacts آدرس
        $mobileFromAddress = null;
        if ($addr && $addr->contacts->isNotEmpty()) {
            $mobileFromAddress = $addr->contacts
                ->where('type', 'mobile')
                ->whereNotNull('value')
                ->pluck('value')
                ->first();
        }

        // نام شخص
        $personName = $person
            ? trim(($person->first_name ?? '') . ' ' . ($person->last_name ?? ''))
            : null;

        // فقط اگر قبلاً چیزی روی خود حمل ثبت نشده باشد، auto-fill کن
        $transport->receiver_company = $transport->receiver_company
            ?? ($company?->display_name ?? $company?->name ?? null);

        $transport->receiver_name = $transport->receiver_name
            ?? ($personName ?: ($company?->display_name ?? $company?->name ?? null));

        // شناسه داخلی / کد ملی گیرنده
        if (! $transport->receiver_national_code && $person) {
            if ($customerScope === 'domestic') {
                $transport->receiver_national_code = $person->national_code;
            } else {
                // foreign → خالی می‌ماند، فقط اگر دستی چیزی وارد شود
                $transport->receiver_national_code = null;
            }
        }

        // موبایل گیرنده از Contacts
        if (! $transport->receiver_mobile && $mobileFromAddress) {
            $transport->receiver_mobile = $mobileFromAddress;
        }

        // آدرس فعالیت و کد پستی
        if (! $transport->receiver_activity_address && $addr) {
            $transport->receiver_activity_address = $addr->address_detail;
        }

        if (! $transport->receiver_postal_code && $addr) {
            $transport->receiver_postal_code = $addr->postal_code;
        }

        // برای نمایش در UI خلاصه
        $countryName  = $addr?->country?->name_fa ?? null;
        $provinceName = $addr?->province?->name_fa ?? null;
        $cityName     = $addr?->city?->name_fa ?? null;

        return view('transports.edit', compact(
            'transport',
            'customerScope',
            'person',
            'company',
            'isPerson',
            'isCompany',
            'countryName',
            'provinceName',
            'cityName'
        ));
    }


    // public function edit(Transport $transport)
    // {
    //     $transport->load([
    //         'preInvoice.customer.persons.addresses',
    //         'preInvoice.customer.companies.addresses',
    //     ]);

    //     $customer = $transport->preInvoice?->customer;

    //     if ($customer) {
    //         $person  = $customer->primaryPerson();
    //         $company = $customer->primaryCompany();

    //         // برای دیباگ موقت:
    //         // dd($customer->toArray(), $person?->toArray(), $company?->toArray());

    //         // آدرس فعالیت: ترجیحاً از شرکت، اگر نبود از شخص
    //         $addr = null;
    //         if ($company && $company->addresses->isNotEmpty()) {
    //             $addr = $company->addresses->first();
    //         } elseif ($person && $person->addresses->isNotEmpty()) {
    //             $addr = $person->addresses->first();
    //         }

    //         $personName = $person
    //             ? trim(($person->first_name ?? '') . ' ' . ($person->last_name ?? ''))
    //             : null;

    //         // فقط اگر قبلاً در فرم چیزی ثبت نشده باشد، مقداردهی کن
    //         $transport->receiver_company = $transport->receiver_company
    //             ?? ($company->name ?? null);

    //         $transport->receiver_name = $transport->receiver_name
    //             ?? ($personName ?: ($company->name ?? null));

    //         $transport->receiver_national_code = $transport->receiver_national_code
    //             ?? ($person->national_code ?? null);

    //         // اگر فعلاً موبایل را جایی ذخیره نمی‌کنی، این را خالی بگذار
    //         $transport->receiver_mobile = $transport->receiver_mobile
    //             ?? null;

    //         $transport->receiver_activity_address = $transport->receiver_activity_address
    //             ?? ($addr->address ?? null);

    //         $transport->receiver_postal_code = $transport->receiver_postal_code
    //             ?? ($addr->postal_code ?? null);
    //     }

    //     return view('transports.edit', compact('transport'));
    // }


    public function update(Request $request, Transport $transport)
    {
        // $this->authorize('edit', $transport);

        // scope مشتری را از روی پیش‌فاکتور بگیر
        $customerScope = $transport->preInvoice?->customer?->customer_scope; // domestic | foreign | null

        // قوانین پایه
        $baseRules = [
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
            // rule کد ملی را بعداً بر اساس scope تنظیم می‌کنیم
            'receiver_mobile'          => ['required', 'string', 'max:30'],
            'receiver_phone'           => ['nullable', 'string', 'max:30'],
            'receiver_activity_address'=> ['required', 'string'],

            'unloading_place_approved' => ['required', 'boolean'],
            'unloading_address'        => ['nullable', 'string'],
            'unloading_postal_code'    => ['nullable', 'string', 'max:20'],
            'unloading_responsible'    => ['nullable', 'string', 'max:255'],
            'unloading_responsible_phone' => ['nullable', 'string', 'max:30'],

            'approve_sales_expert'     => ['required', 'boolean'],
        ];

        // بر اساس داخلی/خارجی بودن مشتری، rule کد ملی گیرنده را تنظیم کن
        if ($customerScope === 'foreign') {
            $baseRules['receiver_national_code'] = ['nullable', 'string', 'max:20'];
        } else {
            // پیش‌فرض یا 'domestic'
            $baseRules['receiver_national_code'] = ['required', 'string', 'max:20'];
        }

        $data = $request->validate($baseRules);

        // اگر دو مرحله‌ای است، نوع واگن الزامی شود
        if ($data['transfer_type'] === 'two_stage' && empty($data['requested_wagon_type'])) {
            return back()
                ->withErrors(['requested_wagon_type' => 'انتخاب نوع واگن الزامی است.'])
                ->withInput();
        }

        // اگر محل تخلیه مورد تایید نیست، فیلدهای مربوطه الزامی شوند
        if (! $data['unloading_place_approved']) {
            $request->validate([
                'unloading_address'           => ['required', 'string'],
                'unloading_postal_code'       => ['required', 'string', 'max:20'],
                'unloading_responsible'       => ['required', 'string', 'max:255'],
                'unloading_responsible_phone' => ['required', 'string', 'max:30'],
            ]);
        }

        // ادامه همان fill و save فعلی‌ات بدون تغییر
        $transport->fill([
            'unloading_confirmed'           => $data['unloading_confirmed'],
            'shipping_type'                 => $data['shipping_type'],
            'transfer_type'                 => $data['transfer_type'],
            'requested_truck_type'          => $data['requested_truck_type'],
            'requested_wagon_type'          => $data['requested_wagon_type'] ?? null,

            'sender_name'                   => $data['sender_name'],
            'sender_postal_code'            => $data['sender_postal_code'],
            'sender_national_code'          => $data['sender_national_code'],
            'sender_phone'                  => $data['sender_phone'],

            'receiver_company'              => $data['receiver_company'],
            'receiver_name'                 => $data['receiver_name'],
            'receiver_postal_code'          => $data['receiver_postal_code'],
            'receiver_national_code'        => $data['receiver_national_code'] ?? null,
            'receiver_phone'                => $data['receiver_phone'] ?? null,
            'receiver_mobile'               => $data['receiver_mobile'],
            'receiver_activity_address'     => $data['receiver_activity_address'],

            'unloading_place_approved'      => $data['unloading_place_approved'],
            'unloading_address'             => $data['unloading_place_approved'] ? null : $data['unloading_address'],
            'unloading_postal_code'         => $data['unloading_place_approved'] ? null : $data['unloading_postal_code'],
            'unloading_responsible'         => $data['unloading_place_approved'] ? null : $data['unloading_responsible'],
            'unloading_responsible_phone'   => $data['unloading_place_approved'] ? null : $data['unloading_responsible_phone'],

            'approved_by_sales_expert'      => $data['approve_sales_expert'],
        ]);

        if ($data['approve_sales_expert']) {
            $transport->status = \App\Enums\TransportStatus::CompletedBySales;
        }

        $transport->save();

        return redirect()
            ->route('pre_invoices.transports.index', $transport->pre_invoice_id)
            ->with('success', 'فرم حمل توسط کارشناس فروش ثبت و تایید شد.');
    }

}

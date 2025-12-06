@extends('layouts.master')

@section('title','چاپ پیش‌فاکتور #'.$pre_invoice->id)

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="print-area">
            <div class="card-body">
                {{-- عنوان و وضعیت --}}
                <div class="invoice-title">
                    <h4 class="float-end font-size-16">
                        پیش‌فاکتور #{{ $pre_invoice->id }}
                        <span class="badge bg-info font-size-12 ms-2">
                            {{ $pre_invoice->status_label }}
                        </span>
                    </h4>
                    <div class="mb-4">
                        {{-- لوگوی شرکت خودت --}}
                        {{-- <img src="{{ asset('assets/images/logo-dark.png') }}" alt="logo" height="20" class="logo-dark" /> --}}
                        {{-- <img src="{{ asset('assets/images/logo-light.png') }}" alt="logo" height="20" class="logo-light" /> --}}
                    </div>
                    <div class="text-muted">
                        {{-- این قسمت را با آدرس شرکت خودت پر کن --}}
                        <p class="mb-1">آدرس شرکت</p>
                        <p class="mb-1"><i class="uil uil-envelope-alt me-1"></i> info@example.com</p>
                        <p><i class="uil uil-phone me-1"></i> 012-3456789</p>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    {{-- اطلاعات مشتری --}}
                    <div class="col-sm-6">
                        <div class="text-muted">
                            <h5 class="font-size-16 mb-3">مشخصات مشتری:</h5>
                            <h5 class="font-size-15 mb-2">
                                {{ $pre_invoice->customer?->first_name }}
                                {{ $pre_invoice->customer?->last_name }}
                            </h5>
                            <p class="mb-1">
                                شرکت:
                                {{ $pre_invoice->customer?->company?->name ?? '-' }}
                            </p>
                            <p class="mb-1">
                                کشور/شهر:
                                {{ $pre_invoice->customer?->country }}
                                /
                                {{ $pre_invoice->customer?->city }}
                            </p>
                            @if($pre_invoice->customer?->email)
                                <p class="mb-1">{{ $pre_invoice->customer->email }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- اطلاعات پیش‌فاکتور / منبع --}}
                    <div class="col-sm-6">
                        <div class="text-muted text-sm-end">
                            <div>
                                <h5 class="font-size-16 mb-1">شماره پیش‌فاکتور:</h5>
                                <p>#{{ $pre_invoice->id }}</p>
                            </div>
                            <div class="mt-3">
                                <h5 class="font-size-16 mb-1">تاریخ:</h5>
                                <p>{{ $pre_invoice->created_at?->format('Y-m-d') }}</p>
                            </div>
                            <div class="mt-3">
                                <h5 class="font-size-16 mb-1">منبع تامین پیشنهادی:</h5>
                                <p>{{ $pre_invoice->source?->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- خلاصه سفارش --}}
                <div class="py-2">
                    <h5 class="font-size-15">خلاصه سفارش</h5>

                    <div class="table-responsive">
                        <table class="table table-nowrap table-centered mb-0">
                            <thead>
                            <tr>
                                <th style="width: 70px;">ردیف</th>
                                <th>کالا</th>
                                <th>قیمت واحد (فروش)</th>
                                <th>تعداد</th>
                                <th class="text-end" style="width: 150px;">مبلغ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $sum = 0; @endphp
                            @foreach($pre_invoice->items as $index => $item)
                                @php
                                    $unitSale  = $item->sale_unit_price ?? $item->unit_price;
                                    $lineTotal = $item->quantity * $unitSale;
                                    $sum      += $lineTotal;
                                @endphp
                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>
                                        <h5 class="font-size-15 mb-1">
                                            {{ $item->product?->name }}
                                        </h5>
                                        @if($item->description)
                                            <p class="mb-0 text-muted" style="white-space: pre-line;">
                                                {{ $item->description }}
                                            </p>
                                        @endif
                                    </td>
                                    <td>{{ number_format($unitSale) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($lineTotal) }}</td>
                                </tr>
                            @endforeach

                            {{-- جمع کل ردیف‌ها --}}
                            <tr>
                                <th scope="row" colspan="4" class="text-end">جمع مبلغ کالاها</th>
                                <td class="text-end">{{ number_format($sum) }}</td>
                            </tr>

                            {{-- افزوده رسمی در صورت وجود --}}
                            @if($pre_invoice->formal_extra)
                                <tr>
                                    <th scope="row" colspan="4" class="border-0 text-end">
                                        افزوده رسمی (مالیات و عوارض)
                                    </th>
                                    <td class="border-0 text-end">
                                        {{ number_format($pre_invoice->formal_extra) }}
                                    </td>
                                </tr>
                            @endif

                            {{-- مبلغ نهایی --}}
                            <tr>
                                <th scope="row" colspan="4" class="border-0 text-end">
                                    مبلغ نهایی پیش‌فاکتور
                                </th>
                                <td class="border-0 text-end">
                                    <h4 class="m-0">{{ number_format($pre_invoice->total_amount ?: $sum) }}</h4>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- شرایط و دکمه‌های چاپ --}}
                    <div class="row mt-4">
                        <div class="col-sm-6">
                            <h5 class="font-size-15 mb-3">شرایط فروش</h5>
                            <p class="mb-1">
                                اعتبار این پیش‌فاکتور:
                                {{ $pre_invoice->validity_days ?? '-' }} روز
                            </p>
                            <p class="mb-1">
                                شرایط پرداخت:
                                {{ $pre_invoice->payment_terms ?? '-' }}
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end d-print-none">
                            <a href="javascript:window.print()" class="btn btn-success waves-effect waves-light me-1">
                                <i class="fa fa-print"></i>
                            </a>
                            <a href="{{ route('pre-invoices.show',$pre_invoice) }}"
                               class="btn btn-primary w-md waves-effect waves-light">
                                بازگشت
                            </a>
                        </div>
                    </div>

                </div> {{-- /py-2 --}}
            </div>
        </div>
    </div>
</div>

@endsection

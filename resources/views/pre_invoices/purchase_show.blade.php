@extends('layouts.master')
@section('title','پیش‌فاکتور خرید #'.$preInvoice->id)

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') پیش‌فاکتور خرید #{{ $preInvoice->id }} @endslot
@endcomponent

<div class="card mb-3">
    <div class="card-body">
        <p>منبع: {{ $preInvoice->source?->last_name }}</p>
        <p>کارشناس خرید: {{ $preInvoice->buyer?->name }}</p>
        <p>پیش‌فاکتور فروش مرجع:
            @if($preInvoice->salePreInvoice)
                <a href="{{ route('pre-invoices.show', $preInvoice->salePreInvoice) }}" target="_blank">
                    #{{ $preInvoice->salePreInvoice->id }}
                </a>
            @else
                -
            @endif
        </p>
        <p>مبلغ کل: {{ number_format($preInvoice->total_amount) }}</p>
        <p>وضعیت: {{ $preInvoice->status_label ?? $preInvoice->status }}</p>
    </div>
</div>


{{-- فقط وقتی در انتظار تایید مالی است --}}
@if($preInvoice->status === \App\Enums\PreInvoiceStatus::WaitingFinancePurchase)
    <div class="card mt-3">
        <div class="card-header">اقدام مالی روی این پیش‌فاکتور خرید</div>
        <div class="card-body">
            {{-- پیام‌ها --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('finance.pre-invoices.purchase-pre-invoices.approve', $preInvoice) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">
                    تایید مالی خرید
                </button>
            </form>

            <button class="btn btn-danger" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#financeRejectForm">
                رد مالی خرید
            </button>

            <div id="financeRejectForm" class="collapse mt-3">
                <form method="POST" action="{{ route('finance.pre-invoices.purchase-pre-invoices.reject', $preInvoice) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">دلیل رد</label>
                        <textarea name="finance_reject_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger">ثبت رد مالی</button>
                </form>
            </div>
        </div>
    </div>
@endif

@if($preInvoice->status === \App\Enums\PreInvoiceStatus::FinancePurchaseApproved)
    <div class="mt-3">
        @if($preInvoice->salePreInvoice)
            <a href="{{ route('pre-invoices.show', $preInvoice->salePreInvoice) }}"
               class="btn btn-secondary">
                بازگشت به پیش‌فاکتور فروش #{{ $preInvoice->salePreInvoice->id }}
            </a>
        @endif
    </div>
@endif

{{-- اکشن‌های مدیر خرید روی پیش‌فاکتور خرید --}}
@if(in_array($preInvoice->status, [
    \App\Enums\PreInvoiceStatus::Draft,
    \App\Enums\PreInvoiceStatus::ApprovedManager, // اگر نیاز داشتی
]))
    <div class="card mt-3">
        <div class="card-header">اقدام مدیر خرید</div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('purchase-manager.purchase-pre-invoices.approve', $preInvoice) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">
                    تایید مدیر خرید برای این پیش‌فاکتور خرید
                </button>
            </form>

            <button class="btn btn-danger" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#purchaseManagerRejectForm">
                رد این پیش‌فاکتور خرید
            </button>

            <div id="purchaseManagerRejectForm" class="collapse mt-3">
                <form method="POST" action="{{ route('purchase-manager.purchase-pre-invoices.reject', $preInvoice) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">دلیل رد (مدیر خرید)</label>
                        <textarea name="purchase_manager_reject_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger">ثبت رد مدیر خرید</button>
                </form>
            </div>
        </div>
    </div>
@endif


<div class="card">
    <div class="card-header">آیتم‌ها</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد خرید</th>
                <th>مبلغ</th>
            </tr>
            </thead>
            <tbody>
            @foreach($preInvoice->purchaseItems as $item)
                @php $lineTotal = $item->quantity * $item->purchase_unit_price; @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->purchase_unit_price) }}</td>
                    <td>{{ number_format($lineTotal) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($preInvoice->direction === 'purchase')
    <h5 class="mt-4">برنامه پرداخت به منبع</h5>

    {{-- لیست برنامه‌های فعلی --}}
    <table class="table table-sm">
        <thead>
        <tr>
            <th>مبلغ</th>
            <th>نوع پرداخت</th>
            <th>تاریخ برنامه‌ریزی‌شده</th>
            <th>توضیح</th>
            <th>تکمیل شده؟</th>
        </tr>
        </thead>
        <tbody>
        @foreach($preInvoice->paymentPlans as $plan)
            <tr>
                <td>{{ number_format($plan->amount) }}</td>
                <td>{{ $plan->payment_type }}</td>
                <td>{{ $plan->scheduled_date }}</td>
                <td>{{ $plan->note }}</td>
                <td>{{ $plan->is_completed ? 'بله' : 'خیر' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- فرم افزودن قسط جدید توسط کارشناس خرید --}}
    <form method="POST" action="{{ route('purchase_pre_invoices.payment_plans.store', $preInvoice->id) }}">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">مبلغ</label>
                <input type="number" name="amount" class="form-control" required min="1000" step="1000">
            </div>
            <div class="col-md-2">
                <label class="form-label">نوع پرداخت</label>
                <select name="payment_type" class="form-select">
                    <option value="cash">نقدی</option>
                    <option value="installment">اقساط</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">تاریخ برنامه‌ریزی‌شده</label>
                <input type="date" name="scheduled_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">توضیح</label>
                <input type="text" name="note" class="form-control">
            </div>
            <div class="col-md-1">
                <button class="btn btn-sm btn-primary w-100">افزودن</button>
            </div>
        </div>
    </form>
@endif


{{-- جدول آیتم‌ها با فرم نهایی‌کردن خرید هر آیتم --}}
<table class="table table-bordered mt-3">
    <thead>
    <tr>
        <th>#</th>
        <th>تغییر</th>
        <th>کالا</th>
        <th>مقدار در فروش</th>
        <th>مقدار نهایی خرید</th>
        <th>قیمت واحد نهایی</th>
        <th>جمع نهایی</th>
        <th>عملیات</th>
    </tr>
    </thead>
    <tbody>
    @foreach($preInvoice->purchaseItems as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>
            
            {{-- لیست assignmentهای این آیتم --}}
            @foreach($item->purchaseAssignments as $assignment)
                @if($assignment->pre_invoice_item_id == $item->id && $assignment->buyer_id == $preInvoice->buyer?->id && $assignment->source_id == $preInvoice->source?->id && $assignment->id == $item->chosen_purchase_assignment_id)
                    <div class="mb-2 border rounded p-1">
                        <div>
                            کارشناس: {{ $assignment->buyer?->name ?? '-' }}
                            | منبع: {{ $assignment->source?->last_name ?? '-' }}
                            | قیمت: {{ number_format($assignment->unit_price) }}
                            | وضعیت: {{ $assignment->status }}
                        </div>

                        {{-- فرم تغییر کارشناس (فقط برای نقش‌های مجاز) --}}
                        @if(auth()->user()->hasRole(['purchase_manager','ceo','it','commerce']))
                            <form method="POST"
                                action="{{ route('purchase_assignments.change_buyer', $assignment->id) }}"
                                class="d-inline">
                                @csrf
                                <select name="buyer_id" class="form-select form-select-sm d-inline-block w-auto">
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}"
                                            @selected($assignment->buyer_id == $buyer->id)>
                                            {{ $buyer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-primary">تغییر کارشناس</button>
                            </form>
                        @endif

                        {{-- فرم تغییر منبع --}}
                        @php $user = auth()->user(); @endphp
                        @if(
                            $user->hasRole(['purchase_manager','ceo','it','commerce'])
                            || ($user->hasRole('purchase_buyer') && $assignment->buyer_id === $user->id)
                        )
                            <form method="POST"
                                action="{{ route('purchase_assignments.change_source', $assignment->id) }}"
                                class="d-inline ms-2">
                                @csrf
                                <select name="source_id" class="form-select form-select-sm d-inline-block w-auto">
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}"
                                            @selected($assignment->source_id == $source->id)>
                                            {{ $source->first_name }} {{ $source->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-secondary">تغییر منبع</button>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach

            
            </td>
            <td>{{ $item->product?->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->final_quantity ?? '-' }}</td>
            <td>{{ $item->final_unit_price ? number_format($item->final_unit_price) : '-' }}</td>
            <td>{{ $item->final_total_price ? number_format($item->final_total_price) : '-' }}</td>
            <td>
                <form method="POST" action="{{ route('purchase_pre_invoices.items.finalize', $item->id) }}" class="row g-1">
                    @csrf
                    <div class="col-4">
                        <input type="number" step="0.001" name="final_quantity"
                               class="form-control form-control-sm"
                               value="{{ old('final_quantity', $item->final_quantity ?? $item->quantity) }}"
                               placeholder="وزن/مقدار نهایی">
                    </div>
                    <div class="col-4">
                        <input type="number" step="0.01" name="final_unit_price"
                               class="form-control form-control-sm"
                               value="{{ old('final_unit_price', $item->final_unit_price ?? $item->unit_price) }}"
                               placeholder="قیمت واحد نهایی">
                    </div>
                    <div class="col-4">
                        <button class="btn btn-sm btn-success w-100">
                            ثبت خرید آیتم
                        </button>
                    </div>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- دکمه تایید خرید کل، مخصوص مدیر خرید --}}
@can('purchase-manager') {{-- هر شرط دسترسی که داری --}}
    <form method="POST" action="{{ route('purchase_pre_invoices.approve_purchase', $preInvoice->id) }}">
        @csrf
        <button class="btn btn-primary mt-3">
            تایید خرید و ارسال به فروش
        </button>
    </form>
@endcan


@endsection

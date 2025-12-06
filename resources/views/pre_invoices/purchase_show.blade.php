@extends('layouts.master')
@section('title','پیش‌فاکتور خرید #'.$preInvoice->id)

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') پیش‌فاکتور خرید #{{ $preInvoice->id }} @endslot
@endcomponent

<div class="card mb-3">
    <div class="card-body">
        <p>منبع: {{ $preInvoice->source?->name }}</p>
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



@endsection

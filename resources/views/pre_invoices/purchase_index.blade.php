@extends('layouts.master')
@section('title','پیش‌فاکتورهای خرید')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') لیست پیش‌فاکتورهای خرید @endslot
@endcomponent

<div class="card">
    <div class="card-header">
        پیش‌فاکتورهای خرید ایجاد شده (بر اساس انتخاب‌های مدیر خرید)
    </div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>منبع</th>
                <th>کارشناس خرید</th>
                <th>پیش‌فاکتور فروش مرجع</th>
                <th>مبلغ کل خرید</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($purchasePreInvoices as $pi)
                <tr>
                    <td>{{ $pi->id }}</td>
                    <td>{{ $pi->source?->name }}</td>
                    <td>{{ $pi->buyer?->name }}</td>
                    <td>
                        @if($pi->salePreInvoice)
                            <a href="{{ route('pre-invoices.show', $pi->salePreInvoice->id) }}" target="_blank">
                                #{{ $pi->salePreInvoice->id }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ number_format($pi->total_amount) }}</td>
                    <td>{{ $pi->status_label ?? $pi->status }}</td>
                    <td>
                        <a href="{{ route('purchase_pre_invoices.purchase_show', $pi) }}"
                        class="btn btn-sm btn-outline-primary">
                            جزئیات
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">هنوز پیش‌فاکتور خریدی ایجاد نشده است.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $purchasePreInvoices->links() }}
    </div>
</div>
@endsection

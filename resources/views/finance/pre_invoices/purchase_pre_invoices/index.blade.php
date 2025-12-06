@extends('layouts.master')
@section('title','پیش‌فاکتورهای خرید در انتظار تایید مالی')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مالی @endslot
    @slot('title') پیش‌فاکتورهای خرید در انتظار تایید مالی @endslot
@endcomponent

<div class="card">
    <div class="card-header">
        پیش‌فاکتورهای خرید (در وضعیت در انتظار تایید مالی)
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

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
            @forelse($preInvoices as $pi)
                <tr>
                    <td>{{ $pi->id }}</td>
                    <td>{{ $pi->source?->name }}</td>
                    <td>{{ $pi->buyer?->name }}</td>
                    <td>
                        @if($pi->salePreInvoice)
                            <a href="{{ route('pre-invoices.show', $pi->salePreInvoice) }}" target="_blank">
                                #{{ $pi->salePreInvoice->id }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ number_format($pi->total_amount) }}</td>
                    <td>{{ $pi->status_label ?? $pi->status }}</td>
                    <td>
                        <a href="{{ route('purchase-pre-invoices.show', $pi) }}"
                           class="btn btn-sm btn-outline-primary">
                            جزئیات / تایید مالی
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        پیش‌فاکتور خریدی در انتظار تایید مالی نیست.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $preInvoices->links() }}
    </div>
</div>
@endsection

@extends('layouts.master')
@section('title','گزارش فاکتورهای بدهکار')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>فاکتورهای بدهکار</span>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($invoices->count() === 0)
            <div class="alert alert-info">
                فاکتور بدهکار فعالی وجود ندارد.
            </div>
        @else
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>شماره فاکتور</th>
                    <th>مشتری</th>
                    <th>مبلغ فاکتور</th>
                    <th>مبلغ پرداخت‌شده</th>
                    <th>مانده بدهی</th>
                    <th>وضعیت</th>
                    <th>تاریخ فاکتور</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoices as $index => $invoice)
                    @php
                        $paid = $invoice->paid_sum ?? 0;
                        $remaining = max(0, $invoice->total_amount - $paid);
                    @endphp
                    <tr>
                        <td>{{ $invoices->firstItem() + $index }}</td>
                        <td>{{ $invoice->id }}</td>
                        <td>
                            {{ $invoice->customer?->first_name }}
                            {{ $invoice->customer?->last_name }}
                        </td>
                        <td>{{ number_format($invoice->total_amount) }}</td>
                        <td>{{ number_format($paid) }}</td>
                        <td class="text-danger">{{ number_format($remaining) }}</td>
                        <td>{{ $invoice->status }}</td>
                        <td>{{ $invoice->created_at?->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('invoices.show',$invoice->id) }}"
                               class="btn btn-sm btn-primary mb-1">مشاهده فاکتور</a>
                            <a href="{{ route('invoices.history',$invoice->id) }}"
                               class="btn btn-sm btn-info">تاریخچه پرداخت</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $invoices->links() }}
        @endif
    </div>
</div>
@endsection

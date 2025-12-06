@extends('layouts.master')
@section('title','تاریخچه پرداخت فاکتور #'.$invoice->id)

@php
    $total  = $invoice->total_amount;
    $paid   = $invoice->payments()
                ->where('status','confirmed')
                ->sum('paid_amount');
    $remain = max(0, $total - $paid);
@endphp

@section('content')
<div class="card mb-3">
    <div class="card-header">اطلاعات فاکتور</div>
    <div class="card-body">
        <p>شماره فاکتور: {{ $invoice->id }}</p>
        <p>مشتری:
            {{ $invoice->customer?->first_name }}
            {{ $invoice->customer?->last_name }}
        </p>
        <p>مبلغ فاکتور: {{ number_format($total) }}</p>
        <p>مبلغ پرداخت‌شده: {{ number_format($paid) }}</p>
        <p>مانده بدهی: {{ number_format($remain) }}</p>
        <p>وضعیت فعلی: {{ $invoice->status }}</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">آیتم‌های فاکتور</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد</th>
                <th>مبلغ</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price) }}</td>
                    <td>{{ number_format($item->total) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">تاریخچه پرداخت‌ها</div>
    <div class="card-body">
        @if($invoice->payments->count() === 0)
            <div class="alert alert-info">
                هنوز هیچ پرداختی برای این فاکتور ثبت نشده است.
            </div>
        @else
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>مبلغ برنامه‌ریزی‌شده</th>
                    <th>مبلغ واقعی پرداخت‌شده</th>
                    <th>تاریخ برنامه‌ریزی‌شده</th>
                    <th>تاریخ واقعی پرداخت</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>مانده پس از این پرداخت</th>
                    <th>فیش</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $runningPaid = 0;
                @endphp
                @foreach($invoice->payments->sortBy('actual_paid_date') as $pay)
                    @php
                        $effectivePaid = $pay->status === 'confirmed'
                            ? ($pay->paid_amount ?? $pay->amount)
                            : 0;
                        $runningPaid += $effectivePaid;
                        $afterDebt = max(0, $total - $runningPaid);
                    @endphp
                    <tr>
                        <td>{{ $pay->id }}</td>
                        <td>{{ number_format($pay->amount) }}</td>
                        <td>{{ $pay->paid_amount ? number_format($pay->paid_amount) : '-' }}</td>
                        <td>{{ $pay->scheduled_date }}</td>
                        <td>{{ $pay->actual_paid_date }}</td>
                        <td>{{ $pay->payment_type }}</td>
                        <td>{{ $pay->status }}</td>
                        <td>{{ number_format($afterDebt) }}</td>
                        <td>
                            @if($pay->receipt_path)
                                <a href="{{ asset('storage/'.$pay->receipt_path) }}" target="_blank">مشاهده فیش</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <a href="{{ route('invoices.show',$invoice->id) }}" class="btn btn-secondary mt-2">
            بازگشت به صفحه فاکتور
        </a>
    </div>
</div>
@endsection

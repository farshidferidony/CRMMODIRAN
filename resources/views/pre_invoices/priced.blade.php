@extends('layouts.master')
@section('title','پیش‌فاکتورهای قیمت‌گذاری‌شده توسط فروش')

@section('content')
<div class="card">
    <div class="card-header">پیش‌فاکتورهای قیمت‌گذاری‌شده توسط فروش</div>
    <div class="card-body">
        @if($preInvoices->isEmpty())
            <div class="alert alert-info">موردی یافت نشد.</div>
        @else
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>شماره</th>
                    <th>مشتری</th>
                    <th>مبلغ کل</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($preInvoices as $index => $pi)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $pi->id }}</td>
                        <td>{{ $pi->customer?->first_name }} {{ $pi->customer?->last_name }}</td>
                        <td>{{ number_format($pi->total_amount) }}</td>
                        <td>{{ $pi->status_label }}</td>
                        <td>
                            <a href="{{ route('pre-invoices.show',$pi->id) }}"
                               class="btn btn-sm btn-primary">
                                مشاهده
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

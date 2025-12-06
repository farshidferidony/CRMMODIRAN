@extends('layouts.master')
@section('title','پیش‌فاکتورهای در انتظار تأیید فروش')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مالی @endslot
    @slot('title') پیش‌فاکتورهای در انتظار تأیید مدیر مالی @endslot
@endcomponent

<div class="card">
    <div class="card-header">پیش‌فاکتورهای در انتظار تأیید</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>مشتری</th>
                <th>جمع کل</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($preInvoices as $pi)
            <tr>
                <td>{{ $pi->id }}</td>
                <td>{{ $pi->customer?->first_name }} {{ $pi->customer?->last_name }}</td>
                <td>{{ number_format($pi->total_amount) }}</td>
                <td>
                    <form method="POST" action="{{ route('finance.pre-invoices.create-invoice',$pi->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-success">ایجاد فاکتور</button>
                    </form>
                </td>
            </tr>
            @endforeach

            </tbody>
        </table>

        {{ $preInvoices->links() }}
    </div>
</div>
@endsection

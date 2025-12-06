@extends('layouts.master')
@section('title','لیست ارجاعات خرید من')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') ارجاعات خرید من @endslot
@endcomponent

<div class="card">
    <div class="card-header">آیتم‌هایی که باید قیمت‌گذاری کنم</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>کد پیش‌فاکتور فروش</th>
                <th>کارشناس خرید</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>منبع</th>
                <th>وضعیت</th>
                <th>قیمت خرید پیشنهادی</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($assignments as $pa)
                <tr>
                    <td>{{ $pa->id }}</td>
                    <td>{{ $pa->item->preInvoice->id }}</td>
                    <td>{{ $pa->buyer?->name ?? '-' }}</td>
                    <td>{{ $pa->item->product?->name }}</td>
                    <td>{{ $pa->item->quantity }}</td>
                    <td>{{ $pa->source?->last_name ?? '-' }}</td>
                    <td>{{ $pa->status }}</td>
                    <td>
                        @if($pa->unit_price)
                            {{ number_format($pa->unit_price) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('buyer.assignments.edit',$pa->id) }}"
                           class="btn btn-sm btn-primary">قیمت‌گذاری/ویرایش</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $assignments->links() }}
    </div>
</div>
@endsection

@extends('layouts.master')
@section('title','پیش‌فاکتورهای آماده ارسال به فروش')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') پیش‌فاکتورهای فروش آماده برای قیمت‌گذاری فروش @endslot
@endcomponent

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>پیش‌فاکتورهای فروش که قیمت خریدشان نهایی شده</span>
    </div>
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>مشتری</th>
                <th>تعداد آیتم</th>
                <th>وضعیت فعلی</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($preInvoices as $pi)
                <tr>
                    <td>{{ $pi->id }}</td>
                    <td>
                        @if($pi->customer)
                            {{ $pi->customer->first_name }} {{ $pi->customer->last_name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $pi->items()->count() }}</td>
                    <td>{{ $pi->status }}</td>
                    <td>
                        <a href="{{ route('purchase-manager.pre-invoices.review',$pi->id) }}"
                           class="btn btn-sm btn-info">
                            جزئیات و انتخاب قیمت‌ها
                        </a>

                        <form method="POST"
                              action="{{ route('purchase-manager.pre-invoices.send-to-sales',$pi->id) }}"
                              style="display:inline-block"
                              onsubmit="return confirm('ارسال این پیش‌فاکتور برای کارشناس فروش؟');">
                            @csrf
                            <button class="btn btn-sm btn-success">
                                ارسال به فروش
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        پیش‌فاکتور فروشی که قیمت خرید آن نهایی شده باشد پیدا نشد.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $preInvoices->links() }}
    </div>
</div>
@endsection

@extends('layouts.master')
@section('title','پیش‌فاکتورهای در انتظار تأیید فروش')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') فروش @endslot
    @slot('title') پیش‌فاکتورهای در انتظار تأیید مدیر فروش @endslot
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
            @forelse($preInvoices as $pi)
                <tr>
                    <td>{{ $pi->id }}</td>
                    <td>{{ $pi->customer?->first_name }} {{ $pi->customer?->last_name }}</td>
                    <td>{{ number_format($pi->total_amount) }}</td>
                    <td>{{ $pi->status }}</td>
                    <td>
                        <a href="{{ route('pre-invoices.show',$pi->id) }}" class="btn btn-sm btn-info">
                            مشاهده و تأیید
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        موردی برای تأیید موجود نیست.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $preInvoices->links() }}
    </div>
</div>
@endsection

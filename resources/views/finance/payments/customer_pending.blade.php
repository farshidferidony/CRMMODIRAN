@extends('layouts.master')

@section('title','پرداخت‌های مشتریان در انتظار تایید مالی')

@section('content')

@component('common-components.breadcrumb')
    @slot('pagetitle') مالی @endslot
    @slot('title') پرداخت‌های در انتظار تایید @endslot
@endcomponent

<div class="row">
    <div class="col-12">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>پرداخت‌های مشتریان در انتظار تایید مالی</span>
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>پیش‌فاکتور</th>
                        <th>مشتری</th>
                        <th>مبلغ برنامه‌ریزی‌شده</th>
                        <th>مبلغ پرداخت‌شده واقعی</th>
                        <th>تاریخ سررسید</th>
                        <th>تاریخ پرداخت واقعی</th>
                        <th>نوع پرداخت</th>
                        <th>وضعیت</th>
                        <th>عملیات مالی</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($payments as $payment)
                        @php
                            $preInvoice = $payment->preInvoice;
                            $customer = $preInvoice?->customer;
                        @endphp
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>
                                @if($preInvoice)
                                    <a href="{{ route('pre_invoices.pre-invoices.show', $preInvoice->id) }}" target="_blank">
                                        #{{ $preInvoice->id }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($customer)
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format($payment->amount) }}</td>
                            <td>
                                {{ $payment->paid_amount ? number_format($payment->paid_amount) : '-' }}
                            </td>
                            <td>{{ $payment->scheduled_date }}</td>
                            <td>{{ $payment->actual_paid_date ?? '-' }}</td>
                            <td>{{ $payment->payment_type }}</td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- تایید مالی --}}
                                    <form method="POST"
                                          action="{{ route('finance.payments.confirm', $payment->id) }}"
                                          onsubmit="return confirm('این پرداخت تایید مالی شود؟');">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            تایید
                                        </button>
                                    </form>

                                    {{-- رد مالی با دلیل --}}
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal-{{ $payment->id }}">
                                        رد
                                    </button>
                                </div>

                                {{-- Modal رد --}}
                                <div class="modal fade" id="rejectModal-{{ $payment->id }}" tabindex="-1"
                                     aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST"
                                                  action="{{ route('finance.payments.reject', $payment->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">رد پرداخت #{{ $payment->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">دلیل رد توسط مالی</label>
                                                        <textarea name="finance_reject_reason"
                                                                  class="form-control"
                                                                  rows="3"
                                                                  required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button"
                                                            class="btn btn-light"
                                                            data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        ثبت رد پرداخت
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-3">
                                پرداخت در انتظار تایید مالی یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="card-footer">
                    {{ $payments->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

@endsection

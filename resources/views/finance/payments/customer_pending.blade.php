@extends('layouts.master')
@section('title','پرداخت‌های مشتری در انتظار تایید مالی')

@section('content')
<div class="card">
    <div class="card-header">پرداخت‌های مشتری در انتظار تایید مالی</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>پیش‌فاکتور</th>
                <th>مشتری</th>
                <th>مبلغ</th>
                <th>نوع</th>
                <th>تاریخ ثبت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payments as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>
                        @if($p->preInvoice)
                            <a href="{{ route('pre-invoices.show', $p->preInvoice) }}" target="_blank">
                                #{{ $p->preInvoice->id }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $p->customer?->full_name }}</td>
                    <td>{{ number_format($p->amount) }}</td>
                    <td>{{ $p->payment_type }}</td>
                    <td>{{ $p->paid_date }}</td>
                    <td>
                        <form method="POST" action="{{ route('finance.payments.confirm', $p) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">تایید</button>
                        </form>

                        <button class="btn btn-danger btn-sm" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#rejectForm{{ $p->id }}">
                            رد
                        </button>

                        <div id="rejectForm{{ $p->id }}" class="collapse mt-2">
                            <form method="POST" action="{{ route('finance.payments.reject', $p) }}">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="finance_reject_reason" class="form-control"
                                              rows="2" placeholder="دلیل رد" required></textarea>
                                </div>
                                <button class="btn btn-danger btn-sm">ثبت رد</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">پرداخت معلقی وجود ندارد.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $payments->links() }}
    </div>
</div>
@endsection

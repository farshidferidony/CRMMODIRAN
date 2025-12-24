{{-- resources/views/transports/index.blade.php --}}
@extends('layouts.master')

@section('content')
    <h4>فرم‌های حمل مربوط به پیش‌فاکتور شماره {{ $preInvoice->id }}</h4>

    <form method="POST" action="{{ route('pre_invoices.transports.store', $preInvoice->id) }}" class="mb-3">
        @csrf
        <button class="btn btn-sm btn-success">
            درخواست فرم حمل جدید
        </button>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>وضعیت</th>
            <th>ایجاد شده در</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @forelse($preInvoice->transports as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->status?->label() }}</td>
                <td>{{ jdate($t->created_at)->format('Y/m/d H:i') }}</td>
                <td>

                   @php
                        $status = $t->status instanceof \App\Enums\TransportStatus
                            ? $t->status->value
                            : $t->status;
                    @endphp

                    @if($status === \App\Enums\TransportStatus::RequestedBySales->value)
                        {{-- هنوز در مرحله کارشناس فروش --}}
                        <a href="{{ route('transports.edit', $t->id) }}"
                        class="btn btn-sm btn-primary">
                            ویرایش (فروش)
                        </a>
                    @elseif($status === \App\Enums\TransportStatus::CompletedBySales->value)
                        {{-- مرحله بعد: کارشناس خرید در همان context پیش‌فاکتور --}}
                        <a href="{{ route('pre_invoices.transports.buy', [$t->pre_invoice_id, $t->id]) }}"
                        class="btn btn-sm btn-warning">
                            مرحله خرید
                        </a>
                    @else
                        <a href="{{ route('pre_invoices.transports.wizard.show', [$preInvoice->id, $t->id]) }}" class="btn btn-sm btn-outline-secondary">
                            مشاهده
                        </a>

                    @endif

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">فرم حملی برای این پیش‌فاکتور ثبت نشده است.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection

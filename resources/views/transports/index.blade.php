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
                <td>{{ $t->status }}</td>
                <td>{{ jdate($t->created_at)->format('Y/m/d H:i') }}</td>
                <td>
                    <a href="{{ route('transports.edit', $t->id) }}" class="btn btn-sm btn-primary">
                        مشاهده / تکمیل فرم حمل
                    </a>
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

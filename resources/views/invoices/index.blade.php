@extends('layouts.master')
@section('title','لیست فاکتورها')

@section('content')
<div class="card">
    <div class="card-header">فاکتورها</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>مشتری</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoices as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->customer?->first_name }} {{ $inv->customer?->last_name }}</td>
                    <td>{{ number_format($inv->total_amount) }}</td>
                    <td>{{ $inv->status }}</td>
                    <td>
                        <a href="{{ route('invoices.show',$inv->id) }}" class="btn btn-sm btn-info">
                            مشاهده
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $invoices->links() }}
    </div>
</div>
@endsection

@extends('layouts.master')
@section('title','پیش‌فاکتورها')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') فروش @endslot
    @slot('title') لیست پیش‌فاکتورها @endslot
@endcomponent

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span>لیست پیش‌فاکتورها</span>
    @can('create', \App\Models\PreInvoice::class)
      <a href="{{ route('pre-invoices.create') }}" class="btn btn-success btn-sm">پیش‌فاکتور جدید</a>
    @endcan
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
          <th>نوع</th>
          <th>وضعیت</th>
          <th>مبلغ کل</th>
          <th>ایجادکننده</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($preInvoices as $pi)
          <tr>
            <td>{{ $pi->id }}</td>
            <td>{{ $pi->customer?->display_name }}</td>
            <td>{{ $pi->type }}</td>
            <td>{{ $pi->status }}</td>
            <td>{{ number_format($pi->total_amount) }}</td>
            <td>{{ $pi->creator?->name ?? $pi->created_by }}</td>
            <td>
              @can('update', $pi)
                <a href="{{ route('pre-invoices.edit',$pi->id) }}"
                   class="btn btn-warning btn-sm">ویرایش</a>
              @endcan
              
                <a href="{{ route('pre-invoices.show',$pi->id) }}"
                   class="btn btn-warning btn-sm">نمایش</a>
             
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{ $preInvoices->links() }}
  </div>
</div>
@endsection

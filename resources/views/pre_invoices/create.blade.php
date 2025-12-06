@extends('layouts.master')
@section('title','پیش‌فاکتور جدید')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') فروش @endslot
    @slot('title') ایجاد پیش‌فاکتور @endslot
@endcomponent

<div class="row">
    <div class="col-lg-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">اطلاعات کلی پیش‌فاکتور</div>
            <div class="card-body">
                <form method="POST" action="{{ route('pre-invoices.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">مشتری</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}"
                                    @if(old('customer_id') == $c->id) selected @endif>
                                    {{ $c->first_name }} {{ $c->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">منبع (اختیاری)</label>
                        <select name="source_id" class="form-control">
                            <option value="">-</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}"
                                    @if(old('source_id') == $s->id) selected @endif>
                                    {{ $s->first_name }} {{ $s->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">نوع پیش‌فاکتور</label>
                        <select name="type" class="form-control" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="normal" @if(old('type')=='normal') selected @endif>عادی</option>
                            <option value="formal" @if(old('type')=='formal') selected @endif>رسمی</option>
                            <option value="export" @if(old('type')=='export') selected @endif>صادراتی</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        ثبت و رفتن به افزودن آیتم‌ها
                    </button>
                    <a href="{{ route('pre-invoices.index') }}" class="btn btn-secondary">بازگشت</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

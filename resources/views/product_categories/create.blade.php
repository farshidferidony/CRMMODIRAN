@extends('layouts.master')
@section('title','ایجاد دسته‌بندی جدید')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت محصولات @endslot
    @slot('title') ایجاد دسته‌بندی جدید @endslot
@endcomponent

<div class="row">
    <div class="col-lg-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('product-categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">نام دسته</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">دسته والد (اختیاری)</label>
                        <select name="parent_id" class="form-control">
                            <option value="">بدون والد (دسته اصلی)</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}" @if(old('parent_id')==$p->id) selected @endif>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success">ذخیره</button>
                    <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">بازگشت</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.master')
@section('title','ویرایش کاربر')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت کاربران @endslot
    @slot('title') ویرایش کاربر @endslot
@endcomponent

<div class="row">
    <div class="col-lg-6">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('users.update',$user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">نام</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name',$user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ایمیل / نام کاربری</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email',$user->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">کلمه عبور جدید (اختیاری)</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">اگر نمی‌خواهید تغییر دهید، خالی بگذارید.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تکرار کلمه عبور</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">بازگشت</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

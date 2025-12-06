@extends('layouts.master')
@section('title','مدیریت دسته‌بندی محصولات')
@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>لیست دسته‌بندی محصولات</span>
        <a href="{{ route('product-categories.create') }}" class="btn btn-success btn-sm">دسته جدید</a>
    </div>
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control"
                           placeholder="جستجو بر اساس نام"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <select name="parent_id" class="form-control">
                        <option value="">- دسته والد -</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}" @if(request('parent_id')==$p->id) selected @endif>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary w-100" type="submit">فیلتر</button>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="{{ route('product-categories.index') }}" class="btn btn-secondary w-100">پاک‌کردن</a>
                </div>
            </div>
        </form>

        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>نام</th>
                <th>والد</th>
                <th>توضیحات</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->id }}</td>
                    <td>{{ $cat->name }}</td>
                    <td>{{ $cat->parent ? $cat->parent->name : '-' }}</td>
                    <td>{{ Str::limit($cat->description,50) }}</td>
                    <td>
                        <a href="{{ route('product-categories.edit',$cat) }}" class="btn btn-warning btn-sm">ویرایش</a>
                        <form method="POST" action="{{ route('product-categories.destroy',$cat) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('حذف دسته؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $categories->links() }}
    </div>
</div>
@endsection

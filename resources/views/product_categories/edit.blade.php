@extends('layouts.master')
@section('title','ویرایش دسته‌بندی')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت محصولات @endslot
    @slot('title') ویرایش دسته‌بندی @endslot
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

                {{-- فرم اصلی ویرایش دسته --}}
                <form method="POST" action="{{ route('product-categories.update',$category->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">نام دسته</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name',$category->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">دسته والد (اختیاری)</label>
                        <select name="parent_id" class="form-control">
                            <option value="">بدون والد (دسته اصلی)</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}"
                                    @if(old('parent_id',$category->parent_id)==$p->id) selected @endif>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" rows="3">
                            {{ old('description',$category->description) }}
                        </textarea>
                    </div>
                    <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                    <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">بازگشت</a>
                </form>

                {{-- از این‌جا به بعد: مدیریت ویژگی‌های دسته --}}
                <hr class="my-4">

                <h5 class="mb-3">ویژگی‌های این دسته</h5>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- فرم افزودن ویژگی جدید --}}
                <form method="POST"
                      action="{{ route('product-attributes.store', $category->id) }}"
                      class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control"
                               placeholder="نام ویژگی (مثلاً سایز)" required>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-control" id="attr-type-new">
                            <option value="text">متنی</option>
                            <option value="number">عددی</option>
                            <option value="select">لیستی (انتخابی)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="values" class="form-control"
                               placeholder="مقادیر برای select (جداشده با , )">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100">+</button>
                    </div>
                </form>

                {{-- لیست ویژگی‌های موجود --}}
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نوع</th>
                        <th>مقادیر (برای select)</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($category->attributes as $attr)
                        <tr>
                            <form method="POST"
                                  action="{{ route('product-attributes.update', $attr->id) }}">
                                @csrf
                                @method('PUT')
                                <td>{{ $attr->id }}</td>
                                <td>
                                    <input type="text" name="name"
                                           class="form-control form-control-sm"
                                           value="{{ $attr->name }}">
                                </td>
                                <td>
                                    <select name="type"
                                            class="form-control form-control-sm attr-type-select">
                                        <option value="text"
                                            @if($attr->type=='text') selected @endif>متنی</option>
                                        <option value="number"
                                            @if($attr->type=='number') selected @endif>عددی</option>
                                        <option value="select"
                                            @if($attr->type=='select') selected @endif>لیستی</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="values"
                                           class="form-control form-control-sm"
                                           value="{{ $attr->values }}"
                                           placeholder="فقط برای select">
                                </td>
                                <td class="d-flex gap-1">
                                    <button type="submit"
                                            class="btn btn-primary btn-sm">ذخیره</button>
                            </form>
                                    <form method="POST"
                                          action="{{ route('product-attributes.destroy', $attr->id) }}"
                                          onsubmit="return confirm('حذف این ویژگی؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">حذف</button>
                                    </form>
                                </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div> {{-- card-body --}}
        </div>     {{-- card --}}
    </div>         {{-- col --}}
</div>             {{-- row --}}
@endsection

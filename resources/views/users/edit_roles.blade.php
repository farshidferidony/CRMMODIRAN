@extends('layouts.master')
@section('title','نقش‌های کاربر')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت کاربران @endslot
    @slot('title') نقش‌های {{ $user->name }} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-6">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                انتخاب نقش‌ها برای: {{ $user->name }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.roles.update',$user->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- نقش‌ها --}}
                    @foreach($roles as $role)
                        <div class="form-check mb-1">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->id }}"
                                   id="role_{{ $role->id }}"
                                   @if(in_array($role->id,$userRoleIds)) checked @endif>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->title }} <span class="text-muted">({{ $role->name }})</span>
                            </label>
                        </div>
                    @endforeach

                    <hr class="my-3">

                    <h5 class="mb-2">دسته‌های تحت سرپرستی / مسئولیت</h5>
                    <p class="text-muted mb-2">
                        برای هر دسته (در صورت نیاز) یکی از نقش‌های سرپرستی/مدیریتی را انتخاب کنید.
                    </p>

                    <table class="table table-sm table-bordered align-middle">
                        <thead>
                        <tr>
                            <th>دسته محصول</th>
                            <th>نقش در این دسته</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $cat)
                            @php
                                $selectedRoleId = $categoryPivot[$cat->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $cat->name }}</td>
                                <td>
                                    <select name="category_roles[{{ $cat->id }}]" class="form-control form-control-sm">
                                        <option value="">هیچ‌کدام</option>
                                        @foreach($roles as $role)
                                            {{-- فقط نقش‌هایی که منطقی هستند را اگر خواستی فیلتر کن --}}
                                            @if(in_array($role->name, ['sales_supervisor','purchase_supervisor','logistics_manager']))
                                                <option value="{{ $role->id }}"
                                                    @if($selectedRoleId == $role->id) selected @endif>
                                                    {{ $role->title }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">ذخیره</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">بازگشت</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

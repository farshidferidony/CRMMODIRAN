@extends('layouts.master')
@section('content')
<div class="card">
  <div class="card-header">لیست مشتریان</div>
  <div class="card-body">

    
    <!-- فرم جستجو و فیلتر -->
    <form method="GET" action="{{ route('customers.index') }}" class="mb-4">
      <div class="row">
          <div class="col-md-3 mb-2">
              <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام یا ایمیل" 
                      value="{{ request('search') }}">
          </div>
          <div class="col-md-3 mb-2">
              <input type="text" name="code" class="form-control" placeholder="کد ملی یا پاسپورت" 
                      value="{{ request('code') }}">
          </div>
          <div class="col-md-3 mb-2">
              <input type="text" name="phone" class="form-control" placeholder="شماره تماس" 
                      value="{{ request('phone') }}">
          </div>
          <div class="col-md-3 mb-2">
              <select name="type" class="form-control">
                  <option value="">- نوع مشتری -</option>
                  <option value="individual" @if(request('type')=='individual') selected @endif>حقیقی</option>
                  <option value="company" @if(request('type')=='company') selected @endif>حقوقی</option>
                  <option value="both" @if(request('type')=='both') selected @endif>حقیقی و حقوقی</option>
              </select>
          </div>
      </div>

      <div class="row">
          <div class="col-md-3 mb-2">
              <select name="company_id" class="form-control">
                  <option value="">- شرکت -</option>
                  @foreach($companies as $company)
                      <option value="{{ $company->id }}" @if(request('company_id')==$company->id) selected @endif>
                          {{ $company->name }}
                      </option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3 mb-2">
            <select name="country_id" class="form-control">
                <option value="">- کشور -</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}" @if(request('country_id') == $country->id) selected @endif>
                        {{ app()->getLocale() == 'fa' ? $country->name_fa : $country->name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 mb-2">
            <select name="province_id" class="form-control" id="filter-province">
                <option value="">- استان -</option>
                {{-- در صورت نیاز با Ajax پر می‌شود --}}
            </select>
        </div>

        <div class="col-md-3 mb-2">
            <select name="city_id" class="form-control" id="filter-city">
                <option value="">- شهر -</option>
                {{-- در صورت نیاز با Ajax پر می‌شود --}}
            </select>
        </div>

      </div>

      <div class="row">
          <div class="col-md-3 mb-2">
              <select name="sort_by" class="form-control">
                  <option value="created_at" @if(request('sort_by')=='created_at') selected @endif>آخرین ایجاد شده</option>
                  <option value="first_name" @if(request('sort_by')=='first_name') selected @endif>نام</option>
                  <option value="updated_at" @if(request('sort_by')=='updated_at') selected @endif>آخرین ویرایش</option>
              </select>
          </div>
          <div class="col-md-2 mb-2">
              <select name="sort_order" class="form-control">
                  <option value="desc" @if(request('sort_order')=='desc') selected @endif>نزولی</option>
                  <option value="asc" @if(request('sort_order')=='asc') selected @endif>صعودی</option>
              </select>
          </div>
          <div class="col-md-2 mb-2">
              <button type="submit" class="btn btn-primary w-100">جستجو</button>
          </div>
          <div class="col-md-2 mb-2">
              <a href="{{ route('customers.index') }}" class="btn btn-secondary w-100">پاک‌کردن</a>
          </div>
          <div class="col-md-3 mb-2">
              <a href="{{ route('customers.create') }}" class="btn btn-success w-100">مشتری جدید</a>
          </div>
      </div>
    </form>


    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th><th>نام</th><th>نوع</th><th>شرکت</th><th>کشور</th><th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
            <td>{{ $customer->type }}</td>
            <td>{{ $customer->company ? $customer->company->name : '-' }}</td>
            <td>
            @php
                $addr = $customer->addresses->first();
            @endphp
            @if($addr && $addr->country)
                {{ app()->getLocale() == 'fa' ? $addr->country->name_fa : $addr->country->name_en }}
            @else
                -
            @endif
            </td>

            <td>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-info btn-sm">مشاهده</a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">ویرایش</a>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                </form>
            </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    {{ $customers->links() }}
  </div>
</div>
@endsection

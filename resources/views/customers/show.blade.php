@extends('layouts.master')
@section('title')
@lang('translation.Profile')
@endsection

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') Contacts @endslot
    @slot('title') Profile @endslot
@endcomponent

<div class="row mb-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-center">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-18" href="#" data-bs-toggle="dropdown">
                            <i class="uil uil-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('customers.edit', $customer->id) }}">ویرایش</a>
                            <a class="dropdown-item" href="#">حذف</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div>
                        <img src="{{ URL::asset('/assets/images/users/avatar-4.jpg') }}" alt="" class="avatar-lg rounded-circle img-thumbnail">
                    </div>

                    {{-- اطلاعات شرکت اگر وجود داشت --}}
                    @if($customer->company)
                        <h5 class="mt-3 mb-1">{{ $customer->company->name }}</h5>
                        <p class="text-muted">مشتری حقوقی</p>
                        <div class="mb-2 mt-2">
                            <span class="badge bg-info">شرکت</span>
                        </div>
                        <div class="text-start mt-3">
                            <h6>اطلاعات شرکت</h6>
                            <ul class="list-unstyled mb-3">
                                <li><strong>نام شرکت:</strong> {{ $customer->company->name }}</li>
                                <li><strong>کد اقتصادی:</strong> {{ $customer->company->economic_code }}</li>
                                <li><strong>شماره ثبت:</strong> {{ $customer->company->registration_number }}</li>
                                <li><strong>ایمیل:</strong> {{ $customer->company->email }}</li>
                            </ul>
                        </div>

                        @if($customer->company->addresses->count())
                            <hr>
                            <div class="text-start mt-2">
                                <h6>آدرس‌های شرکت:</h6>
                                @foreach($customer->company->addresses as $address)
                                    @php
                                        $countryName  = $address->country
                                            ? (app()->getLocale() == 'fa' ? $address->country->name_fa : $address->country->name_en)
                                            : '';
                                        $provinceName = $address->province
                                            ? (app()->getLocale() == 'fa' ? $address->province->name_fa : $address->province->name_en)
                                            : '';
                                        $cityName     = $address->city
                                            ? (app()->getLocale() == 'fa' ? $address->city->name_fa : $address->city->name_en)
                                            : '';
                                    @endphp
                                    <div class="mb-1">
                                        {{ $countryName }} {{ $provinceName }} {{ $cityName }}
                                        - {{ $address->address_detail }}
                                    </div>

                                    @if($address->contacts->count())
                                        <div class="ms-3">
                                            <small class="text-muted">شماره‌های تماس:</small>
                                            @foreach($address->contacts as $contact)
                                                <div>
                                                    {{ $contact->type == 'phone' ? 'تلفن:' : 'موبایل:' }}
                                                    {{ $contact->value }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        <hr>
                    @endif

                    {{-- اطلاعات شخص --}}
                    @if($customer->first_name || $customer->last_name)
                        <h5 class="mt-3 mb-1">{{ $customer->first_name }} {{ $customer->last_name }}</h5>
                        <p class="text-muted">مشتری حقیقی</p>
                        <div class="text-start mt-3">
                            <h6>اطلاعات شخصی</h6>
                            <ul class="list-unstyled mb-3">
                                @if($customer->passport_number)
                                    <li><strong>شماره پاسپورت:</strong> {{ $customer->passport_number }}</li>
                                @endif
                                @if($customer->national_code)
                                    <li><strong>کد ملی:</strong> {{ $customer->national_code }}</li>
                                @endif
                                @if($customer->birthdate)
                                    <li><strong>تاریخ تولد:</strong> {{ $customer->birthdate }}</li>
                                @endif
                                <li><strong>ایمیل:</strong> {{ $customer->email }}</li>
                            </ul>
                        </div>
                    @endif

                    {{-- آدرس و شماره تماس شخص --}}
                    @php
                        $mobiles = [];
                        foreach($customer->addresses as $address) {
                            foreach($address->contacts as $contact) {
                                if($contact->type == 'mobile' && $contact->value) {
                                    $mobiles[] = $contact->value;
                                }
                            }
                        }
                    @endphp

                    @if(!empty($mobiles))
                        <div class="text-start">
                            <h6>شماره‌های موبایل:</h6>
                            @foreach($mobiles as $mob)
                                <div class="mb-1">{{ $mob }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if($customer->addresses->count())
                        <div class="text-start mt-2">
                            <h6>آدرس‌های شخص:</h6>
                            @foreach($customer->addresses as $address)
                                @php
                                    $countryName  = $address->country
                                        ? (app()->getLocale() == 'fa' ? $address->country->name_fa : $address->country->name_en)
                                        : '';
                                    $provinceName = $address->province
                                        ? (app()->getLocale() == 'fa' ? $address->province->name_fa : $address->province->name_en)
                                        : '';
                                    $cityName     = $address->city
                                        ? (app()->getLocale() == 'fa' ? $address->city->name_fa : $address->city->name_en)
                                        : '';
                                @endphp
                                <div class="mb-1">
                                    {{ $countryName }} {{ $provinceName }} {{ $cityName }}
                                    - {{ $address->address_detail }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="button" class="btn btn-light btn-sm">
                            <i class="uil uil-envelope-alt me-2"></i> پیام بده
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- تب‌ها مثل قبل --}}
    <div class="col-xl-8">
        <div class="card mb-0">
            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#about" role="tab">
                        <i class="uil uil-user-circle font-size-20"></i>
                        <span class="d-none d-sm-block">About</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tasks" role="tab">
                        <i class="uil uil-clipboard-notes font-size-20"></i>
                        <span class="d-none d-sm-block">Tasks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#messages" role="tab">
                        <i class="uil uil-envelope-alt font-size-20"></i>
                        <span class="d-none d-sm-block">Messages</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content p-4">
                <div class="tab-pane active" id="about" role="tabpanel">
                    <!-- بعداً پر می‌کنی -->
                </div>
                <div class="tab-pane" id="tasks" role="tabpanel">
                    <!-- بعداً پر می‌کنی -->
                </div>
                <div class="tab-pane" id="messages" role="tabpanel">
                    <!-- بعداً پر می‌کنی -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

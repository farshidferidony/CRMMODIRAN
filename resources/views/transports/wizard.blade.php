@extends('layouts.master')

@section('title')
    پرونده حمل #{{ $transport->id }}
@endsection

@section('content')

@php
    /** @var \App\Models\User $authUser */
    $authUser     = auth()->user();
    $primaryRole  = $authUser->roles()->pluck('name')->first();
    $role         = $primaryRole ?? 'sales_expert';
    $isSuperAdmin = $authUser->isSuperAdmin();

    $canSeeCustomer = in_array($role, [
        'sales_expert','logistics_manager','logistics_expert','accountant','sales_manager'
    ]) || $isSuperAdmin;

    $canSeeSource = in_array($role, [
        'purchase_expert','logistics_manager','logistics_expert','accountant','sales_manager'
    ]) || $isSuperAdmin;
@endphp

@component('common-components.breadcrumb')
    @slot('pagetitle') حمل و نقل @endslot
    @slot('title') پرونده حمل – پیش‌فاکتور {{ $preInvoice->id }} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title mb-4">
                    مدیریت فرم حمل – پیش‌فاکتور {{ $preInvoice->id }}
                </h4>

                @if(session('success'))
                    <div class="alert alert-success small">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger small">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="border rounded p-3 mb-4 bg-light">
                    <div class="d-flex justify-content-between">
                        <div>
                            @if($canSeeCustomer)
                                <strong>مشتری:</strong>
                                {{ $transport->preInvoice->customer?->display_name ?? '-' }}<br>
                                <strong>نوع:</strong>
                                @if($isCompany)
                                    شرکت
                                @elseif($isPerson)
                                    شخص حقیقی
                                @else
                                    نامشخص
                                @endif
                                –
                                <strong>محدوده:</strong>
                                {{ $customerScope === 'foreign' ? 'خارجی' : 'داخلی' }}
                                @if($customerScope === 'foreign' && ($countryName || $provinceName || $cityName))
                                    –
                                    {{ $countryName ?? '-' }}
                                    @if($provinceName) - {{ $provinceName }} @endif
                                    @if($cityName) - {{ $cityName }} @endif
                                @endif
                                <br>
                            @endif

                            @if($canSeeSource)
                                <strong>منبع/محل بارگیری:</strong>
                                <span class="text-muted">
                                    خلاصه آدرس‌های بارگیری پس از ثبت نمایش داده می‌شود.
                                </span>
                            @endif
                        </div>

                        <div class="text-end">
                            <strong>وضعیت فرم حمل:</strong><br>
                            {{ $transport->status?->label() ?? $transport->status }}
                        </div>
                    </div>
                </div>

                <div id="transport-wizard">

                    {{-- STEP 1: اطلاعات فروش --}}
                    <h3>اطلاعات فروش</h3>
                    <section>
                        @php
                            $canEditSales = (($role === 'sales_expert' && ($allowedSteps['sales'] ?? false)) || $isSuperAdmin);
                        @endphp

                        @if($canEditSales)
                            <form method="POST"
                                  action="{{ route('pre_invoices.transports.wizard.update.sales', [$preInvoice->id, $transport->id]) }}">
                                @csrf

                                <div class="row">
                                    <div class="col-lg-8">

                                        {{-- تنظیمات حمل --}}
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <strong>بخش اول: تنظیمات حمل</strong>
                                            </div>
                                            <div class="card-body">

                                                <div class="mb-3">
                                                    <label class="form-label d-block">* تایید تخلیه</label>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input @error('unloading_confirmed') is-invalid @enderror"
                                                               type="radio"
                                                               name="unloading_confirmed"
                                                               id="unloading_confirmed_yes"
                                                               value="1"
                                                               {{ old('unloading_confirmed', $transport->unloading_confirmed) == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="unloading_confirmed_yes">دارد</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input @error('unloading_confirmed') is-invalid @enderror"
                                                               type="radio"
                                                               name="unloading_confirmed"
                                                               id="unloading_confirmed_no"
                                                               value="0"
                                                               {{ old('unloading_confirmed', $transport->unloading_confirmed) === 0 ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="unloading_confirmed_no">ندارد</label>
                                                    </div>
                                                    @error('unloading_confirmed')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* نوع حمل</label>
                                                    <select name="shipping_type" id="shipping_type"
                                                            class="form-control @error('shipping_type') is-invalid @enderror" required>
                                                        <option value="">انتخاب کنید...</option>
                                                        <option value="inner_city"
                                                            {{ old('shipping_type', $transport->shipping_type) === 'inner_city' ? 'selected' : '' }}>
                                                            درون شهری
                                                        </option>
                                                        <option value="outer_city"
                                                            {{ old('shipping_type', $transport->shipping_type) === 'outer_city' ? 'selected' : '' }}>
                                                            برون شهری
                                                        </option>
                                                    </select>
                                                    @error('shipping_type')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                @php
                                                    $transferType = old('transfer_type', $transport->transfer_type);
                                                @endphp
                                                <div class="mb-3">
                                                    <label class="form-label">* نوع انتقال</label>
                                                    <select name="transfer_type" id="transfer_type"
                                                            class="form-control @error('transfer_type') is-invalid @enderror" required>
                                                        <option value="">انتخاب کنید...</option>
                                                        <option value="single_stage"
                                                            {{ $transferType === 'single_stage' ? 'selected' : '' }}>
                                                            تک مرحله‌ای
                                                        </option>
                                                        <option value="two_stage"
                                                            {{ $transferType === 'two_stage' ? 'selected' : '' }}>
                                                            دو مرحله‌ای
                                                        </option>
                                                    </select>
                                                    @error('transfer_type')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3" id="wagon_type_wrapper"
                                                     style="{{ $transferType === 'two_stage' ? '' : 'display:none;' }}">
                                                    <label class="form-label">نوع واگن</label>
                                                    <select name="requested_wagon_type" id="requested_wagon_type"
                                                            class="form-control @error('requested_wagon_type') is-invalid @enderror">
                                                        <option value="">انتخاب کنید...</option>
                                                        <option value="normal"
                                                            {{ old('requested_wagon_type', $transport->requested_wagon_type) === 'normal' ? 'selected' : '' }}>
                                                            معمولی
                                                        </option>
                                                        <option value="russian"
                                                            {{ old('requested_wagon_type', $transport->requested_wagon_type) === 'russian' ? 'selected' : '' }}>
                                                            روسی
                                                        </option>
                                                    </select>
                                                    @error('requested_wagon_type')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* نوع ماشین درخواستی</label>
                                                    <select name="requested_truck_type" id="requested_truck_type"
                                                            class="form-control @error('requested_truck_type') is-invalid @enderror" required>
                                                        <option value="">انتخاب کنید...</option>
                                                        <option value="lowboy"         {{ old('requested_truck_type', $transport->requested_truck_type) === 'lowboy' ? 'selected' : '' }}>کمرشکن</option>
                                                        <option value="flat_trailer"   {{ old('requested_truck_type', $transport->requested_truck_type) === 'flat_trailer' ? 'selected' : '' }}>تریلی کفی</option>
                                                        <option value="roll_trailer"   {{ old('requested_truck_type', $transport->requested_truck_type) === 'roll_trailer' ? 'selected' : '' }}>تریلی جا رول دار</option>
                                                        <option value="side_trailer"   {{ old('requested_truck_type', $transport->requested_truck_type) === 'side_trailer' ? 'selected' : '' }}>تریلی لبه دار</option>
                                                        <option value="ten_wheeler"    {{ old('requested_truck_type', $transport->requested_truck_type) === 'ten_wheeler' ? 'selected' : '' }}>ده چرخ</option>
                                                        <option value="single"         {{ old('requested_truck_type', $transport->requested_truck_type) === 'single' ? 'selected' : '' }}>تک</option>
                                                        <option value="truck_911"      {{ old('requested_truck_type', $transport->requested_truck_type) === 'truck_911' ? 'selected' : '' }}>کامیون ۹۱۱</option>
                                                        <option value="khaawar"        {{ old('requested_truck_type', $transport->requested_truck_type) === 'khaawar' ? 'selected' : '' }}>خاور</option>
                                                        <option value="khaawar_steel"  {{ old('requested_truck_type', $transport->requested_truck_type) === 'khaawar_steel' ? 'selected' : '' }}>خاور آهن‌کش</option>
                                                        <option value="nissan"         {{ old('requested_truck_type', $transport->requested_truck_type) === 'nissan' ? 'selected' : '' }}>نیسان</option>
                                                        <option value="nissan_steel"   {{ old('requested_truck_type', $transport->requested_truck_type) === 'nissan_steel' ? 'selected' : '' }}>نیسان آهن‌کش</option>
                                                        <option value="pickup"         {{ old('requested_truck_type', $transport->requested_truck_type) === 'pickup' ? 'selected' : '' }}>وانت پیکان</option>
                                                        <option value="bunker"         {{ old('requested_truck_type', $transport->requested_truck_type) === 'bunker' ? 'selected' : '' }}>بونکر</option>
                                                    </select>
                                                    @error('requested_truck_type')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            </div>
                                        </div>

                                        {{-- اطلاعات فرستنده --}}
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <strong>بخش دوم: اطلاعات فرستنده</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">* نام فرستنده</label>
                                                    <input type="text" name="sender_name"
                                                           class="form-control @error('sender_name') is-invalid @enderror"
                                                           value="{{ old('sender_name', $transport->sender_name) }}" required>
                                                    @error('sender_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">* کد پستی فرستنده</label>
                                                    <input type="text" name="sender_postal_code"
                                                           class="form-control @error('sender_postal_code') is-invalid @enderror"
                                                           value="{{ old('sender_postal_code', $transport->sender_postal_code) }}" required>
                                                    @error('sender_postal_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">* کد ملی فرستنده</label>
                                                    <input type="text" name="sender_national_code"
                                                           class="form-control @error('sender_national_code') is-invalid @enderror"
                                                           value="{{ old('sender_national_code', $transport->sender_national_code) }}" required>
                                                    @error('sender_national_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">* تلفن فرستنده</label>
                                                    <input type="text" name="sender_phone"
                                                           class="form-control @error('sender_phone') is-invalid @enderror"
                                                           value="{{ old('sender_phone', $transport->sender_phone) }}" required>
                                                    @error('sender_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- اطلاعات گیرنده --}}
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <strong>بخش سوم: اطلاعات گیرنده و محل تخلیه</strong>
                                            </div>
                                            <div class="card-body">
                                                @php
                                                    $isForeign         = isset($customerScope) && $customerScope === 'foreign';
                                                    $unloadingApproved = old('unloading_place_approved', $transport->unloading_place_approved);
                                                @endphp

                                                <div class="mb-3">
                                                    <label class="form-label">* نام شرکت (حقوقی)</label>
                                                    <input type="text" name="receiver_company"
                                                           class="form-control @error('receiver_company') is-invalid @enderror"
                                                           value="{{ old('receiver_company', $transport->receiver_company) }}" required>
                                                    @error('receiver_company')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* نام شخص</label>
                                                    <input type="text" name="receiver_name"
                                                           class="form-control @error('receiver_name') is-invalid @enderror"
                                                           value="{{ old('receiver_name', $transport->receiver_name) }}" required>
                                                    @error('receiver_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* کد پستی گیرنده</label>
                                                    <input type="text" name="receiver_postal_code"
                                                           class="form-control @error('receiver_postal_code') is-invalid @enderror"
                                                           value="{{ old('receiver_postal_code', $transport->receiver_postal_code) }}" required>
                                                    @error('receiver_postal_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        {{ $isForeign ? 'شناسه داخلی (در صورت وجود)' : '* کد ملی گیرنده' }}
                                                    </label>
                                                    <input type="text" name="receiver_national_code"
                                                           class="form-control @error('receiver_national_code') is-invalid @enderror"
                                                           value="{{ old('receiver_national_code', $transport->receiver_national_code) }}"
                                                           {{ $isForeign ? '' : 'required' }}>
                                                    @error('receiver_national_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">تلفن گیرنده</label>
                                                    <input type="text" name="receiver_phone"
                                                           class="form-control @error('receiver_phone') is-invalid @enderror"
                                                           value="{{ old('receiver_phone', $transport->receiver_phone) }}">
                                                    @error('receiver_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* موبایل گیرنده</label>
                                                    <input type="text" name="receiver_mobile"
                                                           class="form-control @error('receiver_mobile') is-invalid @enderror"
                                                           value="{{ old('receiver_mobile', $transport->receiver_mobile) }}" required>
                                                    @error('receiver_mobile')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">* آدرس محل فعالیت</label>
                                                    <textarea name="receiver_activity_address" rows="2"
                                                              class="form-control @error('receiver_activity_address') is-invalid @enderror"
                                                              required>{{ old('receiver_activity_address', $transport->receiver_activity_address) }}</textarea>
                                                    @error('receiver_activity_address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label d-block">* محل تخلیه مورد تایید است؟</label>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input @error('unloading_place_approved') is-invalid @enderror"
                                                               type="radio"
                                                               name="unloading_place_approved"
                                                               id="unloading_place_approved_yes" value="1"
                                                               {{ (string)$unloadingApproved === '1' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="unloading_place_approved_yes">بله</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input @error('unloading_place_approved') is-invalid @enderror"
                                                               type="radio"
                                                               name="unloading_place_approved"
                                                               id="unloading_place_approved_no" value="0"
                                                               {{ (string)$unloadingApproved === '0' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="unloading_place_approved_no">خیر</label>
                                                    </div>
                                                    @error('unloading_place_approved')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div id="unloading_extra_wrapper"
                                                     style="{{ (string)$unloadingApproved === '1' ? 'display:none;' : 'display:block;' }}">
                                                    <div class="mb-3">
                                                        <label class="form-label">* آدرس محل تخلیه</label>
                                                        <textarea name="unloading_address" rows="2"
                                                                  class="form-control @error('unloading_address') is-invalid @enderror">{{ old('unloading_address', $transport->unloading_address) }}</textarea>
                                                        @error('unloading_address')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">* کد پستی محل تخلیه</label>
                                                        <input type="text" name="unloading_postal_code"
                                                               class="form-control @error('unloading_postal_code') is-invalid @enderror"
                                                               value="{{ old('unloading_postal_code', $transport->unloading_postal_code) }}">
                                                        @error('unloading_postal_code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">* مسئول تخلیه / انباردار</label>
                                                        <input type="text" name="unloading_responsible"
                                                               class="form-control @error('unloading_responsible') is-invalid @enderror"
                                                               value="{{ old('unloading_responsible', $transport->unloading_responsible) }}">
                                                        @error('unloading_responsible')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">* شماره تماس مسئول تخلیه</label>
                                                        <input type="text" name="unloading_responsible_phone"
                                                               class="form-control @error('unloading_responsible_phone') is-invalid @enderror"
                                                               value="{{ old('unloading_responsible_phone', $transport->unloading_responsible_phone) }}">
                                                        @error('unloading_responsible_phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <strong>بخش چهارم: تایید کارشناس فروش</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input @error('approve_sales_expert') is-invalid @enderror"
                                                           type="checkbox"
                                                           name="approve_sales_expert" id="approve_sales_expert" value="1">
                                                    <label class="form-check-label" for="approve_sales_expert">
                                                        تایید می‌کنم که اطلاعات فوق صحیح است و فرم حمل برای ادامه مراحل ارسال شود.
                                                    </label>
                                                    @error('approve_sales_expert')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    ثبت اطلاعات فروش
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        @else
                            <p class="text-muted">این بخش فقط توسط کارشناس فروش قابل ویرایش است.</p>
                        @endif
                    </section>

                    {{-- STEP 2: اطلاعات خرید --}}
                    <h3>اطلاعات خرید</h3>
                    <section>
                        @php
                            $canEditPurchase   = (($role === 'purchase_expert' && ($allowedSteps['purchase'] ?? false)) || $isSuperAdmin);
                            $existingLoadings  = old('loadings', $transport->loadings->toArray());
                        @endphp

                        @if($canEditPurchase)
                            <form method="POST"
                                  action="{{ route('pre_invoices.transports.wizard.update.purchase', [$preInvoice->id, $transport->id]) }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <table class="table table-bordered" id="table-loading-places">
                                    <thead>
                                    <tr>
                                        <th>اولویت</th>
                                        <th>کارشناس خرید</th>
                                        <th>نام منبع</th>
                                        <th>تلفن</th>
                                        <th>آدرس</th>
                                        <th>تاریخ تحویل</th>
                                        <th>ردیف/حواله</th>
                                        <th>محصولات و فایل‌ها</th>
                                        <th>حذف</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($existingLoadings as $index => $loading)
                                        <tr class="loadingPlaceTr"
                                            data-index="{{ $index }}"
                                            data-items-json="{{ json_encode($loading['items'] ?? []) }}">
                                            <td>
                                                <input type="number"
                                                       name="loadings[{{ $index }}][priority]"
                                                       class="form-control @error('loadings.'.$index.'.priority') is-invalid @enderror"
                                                       value="{{ $loading['priority'] ?? ($index + 1) }}"
                                                       min="1" required>
                                                @error('loadings.'.$index.'.priority')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[{{ $index }}][buyer_name]"
                                                          class="form-control @error('loadings.'.$index.'.buyer_name') is-invalid @enderror"
                                                          rows="1">{{ $loading['buyer_name'] ?? $authUser->name }}</textarea>
                                                @error('loadings.'.$index.'.buyer_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[{{ $index }}][source_name]"
                                                          class="form-control @error('loadings.'.$index.'.source_name') is-invalid @enderror"
                                                          rows="1">{{ $loading['source_name'] ?? '' }}</textarea>
                                                @error('loadings.'.$index.'.source_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[{{ $index }}][phone]"
                                                          class="form-control @error('loadings.'.$index.'.phone') is-invalid @enderror"
                                                          rows="1" required>{{ $loading['phone'] ?? '' }}</textarea>
                                                @error('loadings.'.$index.'.phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[{{ $index }}][address]"
                                                          class="form-control @error('loadings.'.$index.'.address') is-invalid @enderror"
                                                          rows="2" required>{{ $loading['address'] ?? '' }}</textarea>
                                                @error('loadings.'.$index.'.address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="loadings[{{ $index }}][delivery_time]"
                                                       class="form-control loading-date @error('loadings.'.$index.'.delivery_time') is-invalid @enderror"
                                                       value="{{ isset($loading['delivery_time']) ? \Illuminate\Support\Str::of($loading['delivery_time'])->substr(0,16) : '' }}"
                                                       placeholder="YYYY-MM-DD HH:MM" required>
                                                @error('loadings.'.$index.'.delivery_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="loadings[{{ $index }}][voucher_row]"
                                                       class="form-control @error('loadings.'.$index.'.voucher_row') is-invalid @enderror"
                                                       value="{{ $loading['voucher_row'] ?? '' }}">
                                                @error('loadings.'.$index.'.voucher_row')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary mb-2 btn-select-items"
                                                        data-loading-index="{{ $index }}">
                                                    انتخاب محصول
                                                </button>

                                                <div class="border rounded p-2 mb-2"
                                                     id="loading-items-container-{{ $index }}">
                                                    <small class="text-muted d-block mb-1">
                                                        محصولات انتخابی این آدرس.
                                                    </small>
                                                </div>

                                                <div class="border rounded p-2"
                                                     id="loading-files-container-{{ $index }}">
                                                    <small class="text-muted d-block mb-1">
                                                        فایل‌های پیوست این آدرس.
                                                    </small>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary mt-2 btn-add-file-row"
                                                            data-loading-index="{{ $index }}">
                                                        + افزودن فایل
                                                    </button>
                                                    @error('loadings.'.$index.'.files.*.file')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                    @error('loadings.'.$index.'.files.*.title')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-remove-loading">
                                                    ×
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="loadingPlaceTr"
                                            data-index="0"
                                            data-items-json="[]">
                                            <td>
                                                <input type="number"
                                                       name="loadings[0][priority]"
                                                       class="form-control @error('loadings.0.priority') is-invalid @enderror"
                                                       value="1" min="1" required>
                                                @error('loadings.0.priority')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[0][buyer_name]"
                                                          class="form-control @error('loadings.0.buyer_name') is-invalid @enderror"
                                                          rows="1">{{ $authUser->name }}</textarea>
                                                @error('loadings.0.buyer_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[0][source_name]"
                                                          class="form-control @error('loadings.0.source_name') is-invalid @enderror"
                                                          rows="1"></textarea>
                                                @error('loadings.0.source_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[0][phone]"
                                                          class="form-control @error('loadings.0.phone') is-invalid @enderror"
                                                          rows="1" required></textarea>
                                                @error('loadings.0.phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea name="loadings[0][address]"
                                                          class="form-control @error('loadings.0.address') is-invalid @enderror"
                                                          rows="2" required></textarea>
                                                @error('loadings.0.address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="loadings[0][delivery_time]"
                                                       class="form-control loading-date @error('loadings.0.delivery_time') is-invalid @enderror"
                                                       placeholder="YYYY-MM-DD HH:MM" required>
                                                @error('loadings.0.delivery_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="loadings[0][voucher_row]"
                                                       class="form-control @error('loadings.0.voucher_row') is-invalid @enderror">
                                                @error('loadings.0.voucher_row')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary mb-2 btn-select-items"
                                                        data-loading-index="0">
                                                    انتخاب محصول
                                                </button>

                                                <div class="border rounded p-2 mb-2"
                                                     id="loading-items-container-0">
                                                    <small class="text-muted d-block mb-1">
                                                        محصولات انتخابی این آدرس.
                                                    </small>
                                                </div>

                                                <div class="border rounded p-2"
                                                     id="loading-files-container-0">
                                                    <small class="text-muted d-block mb-1">
                                                        فایل‌های پیوست این آدرس.
                                                    </small>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary mt-2 btn-add-file-row"
                                                            data-loading-index="0">
                                                        + افزودن فایل
                                                    </button>
                                                    @error('loadings.0.files.*.file')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                    @error('loadings.0.files.*.title')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-remove-loading">
                                                    ×
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>

                                <button type="button"
                                        id="btn-add-loading"
                                        class="btn btn-outline-success btn-sm mt-2">
                                    + آدرس جدید
                                </button>

                                <hr>

                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">ارزش کالا (ریال) + ۱۰٪ بیمه</label>
                                        <input type="text" class="form-control"
                                               name="total_value_with_insurance"
                                               value="{{ old('total_value_with_insurance') }}"
                                               readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">وزن تقریبی (کیلوگرم)</label>
                                        <input type="text" class="form-control"
                                               name="total_weight"
                                               value="{{ old('total_weight') }}"
                                               readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">تعداد / مقدار کل</label>
                                        <input type="text" class="form-control"
                                               name="total_quantity"
                                               value="{{ old('total_quantity') }}"
                                               readonly>
                                    </div>
                                </div>

                                <div class="form-check mt-3">
                                    <input class="form-check-input @error('approve_purchase_expert') is-invalid @enderror"
                                           type="checkbox"
                                           name="approve_purchase_expert"
                                           id="approve_purchase_expert"
                                           value="1">
                                    <label class="form-check-label" for="approve_purchase_expert">
                                        تایید اطلاعات محل بارگیری و ارسال به مدیر لجستیک
                                    </label>
                                    @error('approve_purchase_expert')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    ثبت اطلاعات خرید
                                </button>
                            </form>
                        @else
                            <p class="text-muted">
                                این بخش فقط توسط کارشناس خرید قابل ویرایش است؛ سایر نقش‌ها فقط روند را مشاهده می‌کنند.
                            </p>
                        @endif
                    </section>

                    {{-- STEP 3: مدیر لجستیک --}}
                    <h3>مدیر لجستیک</h3>
                    <section>
                        @php
                            $canEditLogisticsManager = (($role === 'logistics_manager' && ($allowedSteps['logistics_manager'] ?? false)) || $isSuperAdmin);

                            // مقدار انتخاب‌شده: یا old فرم، یا مقدار ستون logistics_expert_id مدل
                            $selectedLogisticsUserId = old(
                                'logistics_expert_id',
                                $transport->logistics_expert_id
                            );
                        @endphp

                        @if($canEditLogisticsManager)
                            <form method="POST"
                                action="{{ route('pre_invoices.transports.wizard.update.logistics_manager', [$preInvoice->id, $transport->id]) }}">
                                @csrf

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <strong>تعیین مسئول انجام این فرم حمل</strong>
                                    </div>
                                    <div class="card-body">
                                        {{-- مسئول فرم حمل --}}
                                        <div class="mb-3">
                                            <label class="form-label">* مسئول فرم حمل</label>

                                            <select name="logistics_expert_id"
                                                    class="form-control @error('logistics_expert_id') is-invalid @enderror"
                                                    required>
                                                <option value="">انتخاب کنید...</option>
                                                @foreach($logisticsExperts as $user)
                                                    <option value="{{ $user->id }}"
                                                        {{ (int)$selectedLogisticsUserId === $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                        @php
                                                            $roleNames = $user->roles->pluck('name')->implode('، ');
                                                        @endphp
                                                        @if($roleNames)
                                                            ({{ $roleNames }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('logistics_expert_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                            <small class="text-muted d-block mt-1">
                                                مدیر لجستیک می‌تواند این پرونده را به خودش یا هر کارشناس لجستیک دیگری واگذار کند.
                                            </small>
                                        </div>

                                        {{-- تیک تایید مدیر لجستیک --}}
                                        <div class="form-check mb-3">
                                            <input class="form-check-input @error('approve_logistics_manager') is-invalid @enderror"
                                                type="checkbox"
                                                name="approve_logistics_manager"
                                                id="approve_logistics_manager"
                                                value="1"
                                                {{ old('approve_logistics_manager') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="approve_logistics_manager">
                                                تایید می‌کنم که مسئول فرم حمل به‌درستی انتخاب شده و پرونده برای انجام عملیات حمل به او ارجاع شود.
                                            </label>
                                            @error('approve_logistics_manager')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            ثبت و ارجاع به مسئول حمل
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <p class="text-muted">
                                این بخش فقط توسط مدیر لجستیک قابل ویرایش است. مسئول تعیین‌شده برای این پرونده:
                                <strong>{{ $transport->logisticsExpert?->name ?? 'هنوز تعیین نشده' }}</strong>
                            </p>
                        @endif
                    </section>




                    {{-- STEP 4: کارشناس لجستیک --}}
                    <h3>کارشناس لجستیک</h3>
                    <section>
                        @php
                            $canEditLogisticsExpert = (($role === 'logistics_expert' && ($allowedSteps['logistics_expert'] ?? false)) || $isSuperAdmin);
                            $isTwoStage = $transport->transfer_type === 'two_stage';

                            // trucks و wagons از کنترلر آمده‌اند
                            $oldTrucks = old('trucks');
                            if ($oldTrucks !== null) {
                                $trucksForForm = $oldTrucks;   // آرایه‌ی old (بدون model)
                            } else {
                                $trucksForForm = $trucks;      // آرایه‌ی ساخته‌شده در کنترلر (با model)
                            }

                            $oldWagons = old('wagons');
                            if ($oldWagons !== null) {
                                $wagonsForForm = $oldWagons;
                            } else {
                                $wagonsForForm = $wagons;
                            }
                        @endphp

                        @if($canEditLogisticsExpert)
                            <form method="POST"
                                action="{{ route('pre_invoices.transports.wizard.update.logistics_expert', [$preInvoice->id, $transport->id]) }}"
                                enctype="multipart/form-data">
                                @csrf

                                {{-- بخش ماشین‌ها --}}
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <strong>ماشین‌های حمل جاده‌ای</strong>
                                        <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-truck">
                                            + افزودن ماشین
                                        </button>
                                    </div>
                                    <div class="card-body" id="trucks-container">
                                        @forelse($trucksForForm as $tIndex => $truck)
                                            @include('transports.partials._truck_form_row', [
                                                'index' => $tIndex,
                                                'truck' => $truck,
                                            ])
                                        @empty
                                            @include('transports.partials._truck_form_row', [
                                                'index' => 0,
                                                'truck' => [],
                                            ])
                                        @endforelse
                                    </div>
                                </div>

                                {{-- بخش واگن‌ها --}}
                                @if($isTwoStage)
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <strong>واگن‌های حمل ریلی</strong>
                                            <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-wagon">
                                                + افزودن واگن
                                            </button>
                                        </div>
                                        <div class="card-body" id="wagons-container">
                                            @forelse($wagonsForForm as $wIndex => $wagon)
                                                @include('transports.partials._wagon_form_row', [
                                                    'index' => $wIndex,
                                                    'wagon' => $wagon,
                                                ])
                                            @empty
                                                {{-- هیچ ردیف واگنی نساز؛ فقط دکمه افزودن واگن --}}
                                            @endforelse
                                        </div>
                                    </div>
                                @endif

                                <div class="form-check mb-3">
                                    <input class="form-check-input @error('approve_logistics_expert') is-invalid @enderror"
                                        type="checkbox"
                                        name="approve_logistics_expert"
                                        id="approve_logistics_expert"
                                        value="1"
                                        {{ old('approve_logistics_expert') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="approve_logistics_expert">
                                        تایید می‌کنم اطلاعات عملیات حمل (ماشین‌ها/واگن‌ها) به‌درستی وارد شده است.
                                    </label>
                                    @error('approve_logistics_expert')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    ثبت اطلاعات عملیات حمل
                                </button>
                            </form>
                        @else
                            <p class="text-muted">این بخش فقط توسط کارشناس لجستیک قابل ویرایش است.</p>
                        @endif
                    </section>



                    {{-- STEP 5: حسابداری --}}
                    <h3>حسابداری</h3>
                    <section>
                        <p class="text-muted">در این بخش، وضعیت مالی هر وسیله حمل (ماشین / واگن) بررسی و تأیید می‌شود.</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نوع</th>
                                    <th>شرکت / راننده</th>
                                    <th>مبلغ کرایه</th>
                                    <th>وضعیت حمل</th>
                                    <th>وضعیت حسابداری</th>
                                    <th>عملیات حسابداری</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transport->vehicles as $i => $vehicle)
                                    <tr>
                                        <td>{{ $i+1 }}</td>

                                        {{-- نوع وسیله --}}
                                        <td>
                                            @if($vehicle->is_wagon)
                                                <span class="badge bg-info">واگن</span>
                                            @else
                                                <span class="badge bg-secondary">ماشین</span>
                                            @endif
                                        </td>

                                        {{-- خلاصه شناسایی --}}
                                        <td>
                                            @if($vehicle->is_wagon)
                                                {{ $vehicle->freight_company_name }}
                                            @else
                                                {{ $vehicle->driver_name }}<br>
                                                <small class="text-muted">{{ $vehicle->freight_company_name }}</small>
                                            @endif
                                        </td>

                                        {{-- مبلغ کرایه نمایش برای حسابداری --}}
                                        <td>
                                            @if($vehicle->is_wagon)
                                                {{ number_format($vehicle->wagon_cost) }} ریال
                                            @else
                                                {{ number_format($vehicle->total_freight_amount) }} ریال
                                            @endif
                                        </td>

                                        {{-- وضعیت حمل (لجستیک) فقط جهت اطلاع حسابداری --}}
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $vehicle->status->label() ?? $vehicle->status->value }}
                                            </span>
                                        </td>

                                        {{-- وضعیت حسابداری --}}
                                        <td>
                                            @if($vehicle->freight_accounting_status === \App\Enums\FreightAccountingStatus::Approved)
                                                <span class="badge bg-success">تأیید شده</span>
                                            @elseif($vehicle->freight_accounting_status === \App\Enums\FreightAccountingStatus::Rejected)
                                                <span class="badge bg-danger">رد شده</span>
                                                @if($vehicle->freight_reject_reason)
                                                    <div class="small text-muted mt-1">
                                                        دلیل رد: {{ $vehicle->freight_reject_reason }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge bg-warning text-dark">در انتظار بررسی</span>
                                            @endif
                                        </td>

                                        {{-- عملیات حسابداری روی همین وسیله --}}
                                        <td style="min-width: 260px;">

                                            {{-- فرم تأیید / رد حسابداری --}}
                                            <form method="POST"
                                                action="{{ route('transports.accounting.updateVehicle', [$transport, $vehicle]) }}"
                                                class="d-flex flex-column gap-1">
                                                @csrf
                                                @method('PUT')

                                                <div class="input-group input-group-sm mb-1">
                                                    <span class="input-group-text">پرداخت شده (ریال)</span>
                                                    <input type="number"
                                                        name="freight_paid_amount"
                                                        class="form-control"
                                                        value="{{ old('freight_paid_amount') }}">
                                                </div>


                                                {{-- فایل‌های واریزی --}}
                                                <div class="mb-1">
                                                    <input type="file" name="files[]" class="form-control form-control-sm" multiple>
                                                    <small class="text-muted">فایل‌های واریزی (رسیدها، حواله‌ها و ...)</small>
                                                </div>

                                                {{-- فقط وقتی هنوز در انتظار بررسی است، دکمه تایید/رد را نشان بده --}}
                                                @if($vehicle->freight_accounting_status === \App\Enums\FreightAccountingStatus::Pending)
                                                    <div class="d-flex gap-1 mb-1">
                                                        <button type="submit"
                                                                name="action"
                                                                value="approve"
                                                                class="btn btn-success btn-sm flex-fill">
                                                            تأیید هزینه
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm flex-fill js-show-reject-reason"
                                                                data-vehicle-id="{{ $vehicle->id }}">
                                                            رد هزینه
                                                        </button>

                                                    </div>

                                                    {{-- فیلد دلیل رد، اول مخفی --}}
                                                    <div class="mb-1 js-reject-reason-wrapper d-none" id="reject-wrapper-{{ $vehicle->id }}">
                                                        <div class="input-group input-group-sm mb-1">
                                                            <span class="input-group-text">دلیل رد</span>
                                                            <input type="text"
                                                                name="freight_reject_reason"
                                                                class="form-control"
                                                                value="">
                                                        </div>

                                                        <button type="submit"
                                                                name="action"
                                                                value="reject"
                                                                class="btn btn-danger btn-sm w-100">
                                                            ثبت رد هزینه
                                                        </button>
                                                    </div>

                                                @endif


                                                {{-- اگر قبلاً تأیید شده، گزینه ثبت پرداخت و تسویه را نشان بده --}}
                                                @if($vehicle->freight_accounting_status === \App\Enums\FreightAccountingStatus::Approved)
                                                    <div class="mt-1">
                                                        <div class="input-group input-group-sm mb-1">
                                                            <span class="input-group-text">تاریخ پرداخت</span>
                                                            <input type="datetime-local"
                                                                name="freight_paid_at"
                                                                class="form-control"
                                                                value="{{ $vehicle->freight_paid_at?->format('Y-m-d\TH:i') }}">
                                                        </div>

                                                        <div class="form-check form-check-sm">
                                                            <input class="form-check-input"
                                                                type="checkbox"
                                                                name="freight_settled"
                                                                value="1"
                                                                id="settled_{{ $vehicle->id }}"
                                                                {{ $vehicle->freight_settled ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="settled_{{ $vehicle->id }}">
                                                                این وسیله تسویه شده است
                                                            </label>
                                                        </div>

                                                        <button type="submit"
                                                                name="action"
                                                                value="settle"
                                                                class="btn btn-primary btn-sm mt-1">
                                                            ثبت پرداخت و تسویه
                                                        </button>
                                                    </div>
                                                @endif
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- گزینه: تسویه کل هزینه حمل این فرم حمل --}}
                        <form method="POST" action="{{ route('transports.accounting.settleAll', $transport) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settle_all" value="1" id="settle_all">
                                <label class="form-check-label" for="settle_all">
                                    تأیید می‌کنم همه‌ی هزینه‌های حمل این فرم حمل پرداخت و تسویه شده است.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-outline-primary btn-sm mt-2">
                                ثبت تسویه کامل این فرم حمل
                            </button>
                        </form>
                    </section>


                    {{-- STEP 6: مدیر فروش --}}
                    <h3>مدیر فروش</h3>
                    <section>
                        @if(!$transport->canShowSalesManagerStep())
                            <p class="text-muted">
                                این بخش زمانی فعال می‌شود که حداقل یکی از ماشین‌ها وارد مرحله بارگیری شده باشد.
                            </p>
                        @else
                            @include('transports.partials.sales_manager_table', ['transport' => $transport])
                        @endif
                    </section>




                    {{-- STEP 7: بستن پرونده --}}
                    <h3>بستن پرونده</h3>
                    <section>
                        @if(!$transport->canCloseByLogistics())
                            <p class="text-muted">
                                برای بستن پرونده، باید تمام آیتم‌ها تخلیه کامل شده، همه مبالغ توسط حسابداری تسویه،
                                و تأیید مدیر فروش ثبت شده باشد.
                            </p>
                        @else
                            <form method="POST" action="{{ route('transports.close', $transport) }}">
                                @csrf
                                @method('PUT')

                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="process_completed"
                                        value="1"
                                        id="process_completed"
                                        required>
                                    <label class="form-check-label" for="process_completed">
                                        تأیید می‌کنم تمام فرآیندها (لجستیک، حسابداری، مدیر فروش) انجام شده است و پرونده قابل بستن است.
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-danger btn-sm mt-2">
                                    بستن نهایی پرونده حمل
                                </button>
                            </form>
                        @endif
                    </section>


                </div> {{-- #transport-wizard --}}

            </div>
        </div>
    </div>
</div>

{{-- Modal انتخاب محصول --}}
<div class="modal fade" id="productsModal" tabindex="-1" aria-labelledby="productsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productsModalLabel">
                    انتخاب محصول برای آدرس <span id="productsModalAddressLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped align-middle" id="products-table">
                    <thead>
                    <tr>
                        <th>انتخاب</th>
                        <th>محصول</th>
                        <th>منبع</th>
                        <th>مقدار کل</th>
                        <th>واحد</th>
                        <th>قیمت واحد</th>
                        <th>مقدار برای این آدرس</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($preInvoiceProducts as $row)
                        @php
                            $product = $row->product;
                            $assign  = $row->chosenPurchaseAssignment;
                            $source  = $assign?->source;
                            $buyer   = $assign?->buyer;

                            $company      = $source?->company;
                            $addresses    = $source?->addresses ?? collect();
                            $addressModel = $addresses->first();

                            $phoneContact = null;
                            if ($addressModel) {
                                $phoneContact = $addressModel->contacts
                                    ->first(function ($c) {
                                        return in_array($c->type, ['phone', 'mobile', 'tel']);
                                    }) ?? $addressModel->contacts->first();
                            }

                            $sourceName = $company?->name
                                ?? trim(($source->first_name ?? '').' '.($source->last_name ?? ''));

                            $sourcePhone = $phoneContact?->value;

                            $sourceAddressParts = [];
                            if ($addressModel?->country)  $sourceAddressParts[] = $addressModel->country->name_fa ?? $addressModel->country->name ?? '';
                            if ($addressModel?->province) $sourceAddressParts[] = $addressModel->province->name_fa ?? $addressModel->province->name ?? '';
                            if ($addressModel?->city)     $sourceAddressParts[] = $addressModel->city->name_fa ?? $addressModel->city->name ?? '';
                            if ($addressModel?->address_detail) $sourceAddressParts[] = $addressModel->address_detail;
                            if ($addressModel?->postal_code)    $sourceAddressParts[] = 'کد پستی: '.$addressModel->postal_code;
                            $sourceAddress = implode(' - ', array_filter($sourceAddressParts));

                            $buyerName = $buyer?->name ?? $authUser->name;

                            $unit      = 'kg';
                            $qty       = $row->final_purchase_weight ?? $row->quantity;
                            $unitPrice = $row->purchase_unit_price ?? $row->unit_price;
                        @endphp

                        <tr
                            data-product-id="{{ $product->id }}"
                            data-source-id="{{ $source?->id }}"
                            data-source-name="{{ $sourceName }}"
                            data-source-phone="{{ $sourcePhone }}"
                            data-source-address="{{ $sourceAddress }}"
                            data-buyer-name="{{ $buyerName }}"
                            data-unit="{{ $unit }}"
                            data-quantity="{{ $qty }}"
                            data-unit-price="{{ $unitPrice }}"
                            data-used="0">
                            <td>
                                <input type="checkbox" class="product-select-checkbox">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $sourceName }}</td>
                            <td>{{ number_format($qty) }}</td>
                            <td>{{ $unit }}</td>
                            <td>{{ number_format($unitPrice) }}</td>
                            <td style="width: 150px;">
                                <input type="number" class="form-control form-control-sm product-qty-input"
                                       min="0" step="0.001">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <small class="text-muted">
                    در هر آدرس بارگیری فقط محصولات یک منبع قابل انتخاب هستند.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" id="btn-save-products-for-loading">ثبت محصولات</button>
            </div>
        </div>
    </div>
</div>

{{-- Template ردیف فایل --}}
<div id="file-input-template" style="display:none;">
    <div class="file-row mb-2">
        <div class="row g-2">
            <div class="col-md-6">
                <input type="file" class="form-control file-input">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control file-description-input" placeholder="عنوان / توضیح فایل">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-file-row">×</button>
            </div>
        </div>
    </div>
</div>

{{--  انتخاب محصول برای حمل --}}
<div class="modal fade" id="vehicleItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    انتخاب آیتم‌ها برای وسیله حمل
                    <span id="vehicleItemsModalLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped align-middle" id="vehicle-items-table">
                    <thead>
                    <tr>
                        <th>انتخاب</th>
                        <th>آدرس بارگیری</th>
                        <th>محصول</th>
                        <th>مقدار کل قابل تخصیص</th>
                        <th>واحد</th>
                        <th>مقدار برای این وسیله</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transport->loadings as $loading)
                        @foreach($loading->items as $item)
                            @php
                                $product = $item->product;
                                $unit    = $item->unit ?? 'kg';
                                $qty     = $item->quantity;
                            @endphp
                            <tr
                                data-loading-id="{{ $loading->id }}"
                                data-product-id="{{ $product->id }}"
                                data-available-qty="{{ $qty }}"
                                data-unit="{{ $unit }}">
                                <td>
                                    <input type="checkbox" class="vehicle-item-checkbox">
                                </td>
                                <td>{{ $loading->address_short ?? $loading->address }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($qty) }}</td>
                                <td>{{ $unit }}</td>
                                <td style="width:150px;">
                                    <input type="number"
                                           class="form-control form-control-sm vehicle-item-qty"
                                           min="0" step="0.001">
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
                <small class="text-muted">
                    مقدار انتخابی برای هر محصول نباید از مقدار باقی‌مانده‌اش در کل حمل بیشتر شود.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" id="btn-save-vehicle-items">ثبت آیتم‌ها</button>
            </div>
        </div>
    </div>
</div>

{{-- Templates for dynamic rows --}}
<div id="truck-row-template" class="d-none">
    @php $idx = '__INDEX__'; @endphp
    @include('transports.partials._truck_form_row', [
        'index' => $idx,
        'truck' => [],
    ])
</div>

<div id="wagon-row-template" class="d-none">
    @php $idx = '__INDEX__'; @endphp
    @include('transports.partials._wagon_form_row', [
        'index' => $idx,
        'wagon' => [],
    ])
</div>



@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/jquery-steps/jquery-steps.min.js') }}"></script>

    <script>
        // ===== تاریخ‌های آدرس‌های بارگیری (Step 2) =====
        function initLoadingDatepickers(context) {
            $(context).find('.loading-date').each(function () {
                if (!$(this).data('datepicker')) {
                    $(this).persianDatepicker({
                        format: 'YYYY-MM-DD HH:mm',
                        timePicker: {
                            enabled: true
                        }
                    });
                }
            });
        }

        // ===== تاریخ‌های ماشین/واگن (Step 4) =====
        function initVehicleDatepickers(context) {
            $(context).find('.vehicle-date').each(function () {
                if (!$(this).data('datepicker')) {
                    console.log(this.value);
                    if(this.value != ""){
                        console.log("true");
                        $(this).persianDatepicker({
                            format: 'YYYY-MM-DD HH:mm',
                            timePicker: { enabled: true }
                        });
                    }else{
                        console.log("false");
                        $(this).persianDatepicker({
                            format: 'YYYY-MM-DD HH:mm',
                            timePicker: { enabled: true },
                            initialValue: false
                        });
                    }
                    
                }
            });
        }

        $(function () {
            // ===== init wizard =====
            $("#transport-wizard").steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "slideLeft",
                startIndex: {{ $currentStepIndex }},
                labels: {
                    finish: "اتمام",
                    next: "بعدی",
                    previous: "قبلی"
                },
                onStepChanging: function () { return true; }
            });

            // نمایش/عدم نمایش نوع واگن
            $(document).on('change', '#transfer_type', function () {
                $('#wagon_type_wrapper').toggle($(this).val() === 'two_stage');
            });

            // نمایش/عدم نمایش آدرس تخلیه اضافی
            $(document).on('change', 'input[name="unloading_place_approved"]', function () {
                $('#unloading_extra_wrapper').toggle($(this).val() !== '1');
            });

            // فعال‌سازی دیت‌پیکر برای فیلدهای موجود
            initLoadingDatepickers($(document));
            initVehicleDatepickers($(document));

            // =========================================================
            //        Step 2: مدیریت آدرس‌های بارگیری و محصولات
            // =========================================================

            let loadingIndex = $('#table-loading-places tbody tr.loadingPlaceTr').length || 1;

            $('#btn-add-loading').on('click', function () {
                const idx = loadingIndex++;
                const rowHtml = `
                <tr class="loadingPlaceTr"
                    data-index="${idx}"
                    data-items-json="[]">
                    <td>
                        <input type="number" name="loadings[${idx}][priority]"
                            class="form-control"
                            value="${idx+1}" min="1" required>
                    </td>
                    <td>
                        <textarea name="loadings[${idx}][buyer_name]"
                                class="form-control"
                                rows="1">{{ $authUser->name }}</textarea>
                    </td>
                    <td>
                        <textarea name="loadings[${idx}][source_name]"
                                class="form-control"
                                rows="1"></textarea>
                    </td>
                    <td>
                        <textarea name="loadings[${idx}][phone]"
                                class="form-control"
                                rows="1" required></textarea>
                    </td>
                    <td>
                        <textarea name="loadings[${idx}][address]"
                                class="form-control"
                                rows="2" required></textarea>
                    </td>
                    <td>
                        <input type="text"
                            name="loadings[${idx}][delivery_time]"
                            class="form-control loading-date"
                            placeholder="YYYY-MM-DD HH:MM" required>
                    </td>
                    <td>
                        <input type="text"
                            name="loadings[${idx}][voucher_row]"
                            class="form-control">
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary mb-2 btn-select-items"
                                data-loading-index="${idx}">
                            انتخاب محصول
                        </button>

                        <div class="border rounded p-2 mb-2"
                            id="loading-items-container-${idx}">
                            <small class="text-muted d-block mb-1">محصولات انتخابی این آدرس.</small>
                        </div>

                        <div class="border rounded p-2"
                            id="loading-files-container-${idx}">
                            <small class="text-muted d-block mb-1">فایل‌های پیوست این آدرس.</small>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary mt-2 btn-add-file-row"
                                    data-loading-index="${idx}">
                                + افزودن فایل
                            </button>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button"
                                class="btn btn-sm btn-danger btn-remove-loading">×</button>
                    </td>
                </tr>`;
                const $row = $(rowHtml);
                $('#table-loading-places tbody').append($row);
                initLoadingDatepickers($row);
            });

            $(document).on('click', '.btn-remove-loading', function () {
                $(this).closest('tr.loadingPlaceTr').remove();
                recalcTotals();
                recalcUsedPerProduct();
            });

            // ====== انتخاب محصول با مدیریت مقدار باقی‌مانده ======
            let currentLoadingIndex = null;

            $(document).on('click', '.btn-select-items', function () {
                currentLoadingIndex = $(this).data('loading-index');
                $('#productsModalAddressLabel').text(currentLoadingIndex + 1);

                recalcUsedPerProduct();

                // ریست اولیه
                $('#products-table tbody tr').each(function () {
                    const $tr  = $(this);
                    const $chk = $tr.find('.product-select-checkbox');
                    const $qtyInput = $tr.find('.product-qty-input');

                    $chk.prop('checked', false).prop('disabled', false);
                    $qtyInput.val('').prop('disabled', false);
                    $tr.removeClass('table-secondary');
                });

                const $row = $('tr.loadingPlaceTr[data-index="'+ currentLoadingIndex +'"]');
                let itemsJson = $row.attr('data-items-json') || '[]';
                let existingItems;
                try {
                    existingItems = JSON.parse(itemsJson);
                } catch (e) {
                    existingItems = [];
                }

                // آزاد کردن سهم این آدرس و برگرداندن مقادیرش
                existingItems.forEach(function (item) {
                    const $tr = $('#products-table tbody tr[data-product-id="'+ item.product_id +'"]');
                    if (!$tr.length) return;

                    const totalQty   = parseFloat($tr.data('quantity'));
                    const usedGlobal = parseFloat($tr.data('used') || 0);

                    const usedWithoutThis   = usedGlobal - parseFloat(item.quantity);
                    const remainingForThis  = totalQty - usedWithoutThis;

                    $tr.data('used', usedWithoutThis);

                    const $chk = $tr.find('.product-select-checkbox');
                    const $qtyInput = $tr.find('.product-qty-input');

                    if (remainingForThis <= 0) {
                        $chk.prop('checked', false).prop('disabled', true);
                        $qtyInput.val('').prop('disabled', true);
                        $tr.addClass('table-secondary');
                        return;
                    }

                    $chk.prop('disabled', false).prop('checked', true);
                    $qtyInput.prop('disabled', false).val(item.quantity);
                });

                // اعمال remaining برای سایر محصولات
                $('#products-table tbody tr').each(function () {
                    const $tr  = $(this);
                    const totalQty = parseFloat($tr.data('quantity'));
                    const usedNow  = parseFloat($tr.data('used') || 0);
                    const remaining = totalQty - usedNow;

                    const $chk = $tr.find('.product-select-checkbox');
                    const $qtyInput = $tr.find('.product-qty-input');

                    if (remaining <= 0) {
                        $chk.prop('checked', false).prop('disabled', true);
                        $qtyInput.val('').prop('disabled', true);
                        $tr.addClass('table-secondary');
                    }
                });

                const modalEl = document.getElementById('productsModal');
                const modal   = new bootstrap.Modal(modalEl);
                modal.show();
            });

            $('#btn-save-products-for-loading').on('click', function () {
                if (currentLoadingIndex === null) return;

                let sourceIds     = {};
                let selectedItems = [];
                let hasError      = false;

                $('#products-table tbody tr').each(function () {
                    const $tr  = $(this);
                    const $chk = $tr.find('.product-select-checkbox');
                    if (!$chk.is(':checked') || $chk.is(':disabled')) return;

                    const productId   = $tr.data('product-id');
                    const sourceId    = $tr.data('source-id');
                    const sourceName  = $tr.data('source-name');
                    const unit        = $tr.data('unit');
                    const totalQty    = parseFloat($tr.data('quantity'));
                    const usedGlobal  = parseFloat($tr.data('used') || 0);

                    const qtyInputVal = $tr.find('.product-qty-input').val();
                    const qty         = parseFloat(qtyInputVal || 0);

                    if (!qty || qty <= 0) {
                        alert('برای محصولات انتخاب شده، مقدار معتبر وارد کنید.');
                        hasError = true;
                        return false;
                    }

                    const $row = $('tr.loadingPlaceTr[data-index="'+ currentLoadingIndex +'"]');
                    let itemsJson = $row.attr('data-items-json') || '[]';
                    let existingItems;
                    try {
                        existingItems = JSON.parse(itemsJson);
                    } catch (e) {
                        existingItems = [];
                    }

                    let prevQtyThisAddress = 0;
                    existingItems.forEach(function (item) {
                        if (parseInt(item.product_id) === parseInt(productId)) {
                            prevQtyThisAddress = parseFloat(item.quantity);
                        }
                    });

                    const usedOtherAddresses = usedGlobal - prevQtyThisAddress;
                    const remaining = totalQty - usedOtherAddresses;

                    if (qty > remaining) {
                        alert('مقدار انتخابی برای محصول '+ productId +' از مقدار باقی‌مانده مجاز بیشتر است (حداکثر '+ remaining +').');
                        hasError = true;
                        return false;
                    }

                    sourceIds[sourceId] = true;
                    selectedItems.push({
                        product_id:      productId,
                        source_id:       sourceId,
                        source_name:     sourceName,
                        source_phone:    $tr.data('source-phone') || '',
                        source_address:  $tr.data('source-address') || '',
                        buyer_name:      $tr.data('buyer-name') || '',
                        unit:            unit,
                        max_quantity:    totalQty,
                        quantity:        qty,
                        unit_price:      parseFloat($tr.data('unit-price'))
                    });
                });

                if (hasError) return;

                if (!selectedItems.length) {
                    persistItems(currentLoadingIndex, []);
                    updateLoadingRowFromItems(currentLoadingIndex, []);
                    recalcTotals();
                    recalcUsedPerProduct();
                    const modalEl = document.getElementById('productsModal');
                    const modal   = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    return;
                }

                if (Object.keys(sourceIds).length > 1) {
                    alert('در هر آدرس بارگیری فقط محصولات یک منبع می‌توانند انتخاب شوند. لطفاً آدرس جدید بسازید.');
                    return;
                }

                persistItems(currentLoadingIndex, selectedItems);
                updateLoadingRowFromItems(currentLoadingIndex, selectedItems);
                recalcTotals();
                recalcUsedPerProduct();

                const modalEl = document.getElementById('productsModal');
                const modal   = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            });

            function persistItems(loadingIdx, items) {
                const $row = $('tr.loadingPlaceTr[data-index="'+ loadingIdx +'"]');
                $row.attr('data-items-json', JSON.stringify(items || []));
            }

            function updateLoadingRowFromItems(loadingIdx, items) {
                const $row  = $('tr.loadingPlaceTr[data-index="'+ loadingIdx +'"]');
                const $sourceName = $row.find('textarea[name="loadings['+ loadingIdx +'][source_name]"]');
                const $phone      = $row.find('textarea[name="loadings['+ loadingIdx +'][phone]"]');
                const $address    = $row.find('textarea[name="loadings['+ loadingIdx +'][address]"]');
                const $buyer      = $row.find('textarea[name="loadings['+ loadingIdx +'][buyer_name]"]');

                const $itemsContainer = $('#loading-items-container-' + loadingIdx);
                $itemsContainer.empty();

                if (!items.length) {
                    $itemsContainer.append('<small class="text-muted d-block mb-1">محصولی انتخاب نشده است.</small>');
                    return;
                }

                const first = items[0];

                // خریدار را همیشه از items بگیر
                $buyer.val(first.buyer_name || '{{ $authUser->name }}');

                // منبع/تلفن/آدرس را فقط اگر خالی‌اند پر کن
                if (!$sourceName.val()) $sourceName.val(first.source_name || '');
                if (!$phone.val())      $phone.val(first.source_phone || '');
                if (!$address.val())    $address.val(first.source_address || '');

                items.forEach(function (item) {
                    const base = 'loadings['+ loadingIdx +'][items]['+ item.product_id +']';
                    const valueWithInsurance = item.quantity * item.unit_price * 1.1;

                    const html = `
                        <div class="small mb-1">
                            <span class="badge bg-light text-dark">
                                محصول ${item.product_id} | مقدار: ${item.quantity} ${item.unit} | منبع: ${item.source_name}
                            </span>
                            <input type="hidden" name="${base}[product_id]" value="${item.product_id}">
                            <input type="hidden" name="${base}[quantity]" value="${item.quantity}">
                            <input type="hidden" name="${base}[unit]" value="${item.unit}">
                            <input type="hidden" name="${base}[unit_price]" value="${item.unit_price}">
                            <input type="hidden" name="${base}[value_with_insurance]" value="${valueWithInsurance}">
                        </div>`;
                    $itemsContainer.append(html);
                });
            }

            function recalcTotals() {
                let totalValue  = 0;
                let totalWeight = 0;
                let totalQty    = 0;

                $('tr.loadingPlaceTr').each(function () {
                    const $row = $(this);
                    let itemsJson = $row.attr('data-items-json') || '[]';
                    let items;
                    try {
                        items = JSON.parse(itemsJson);
                    } catch (e) {
                        items = [];
                    }

                    items.forEach(function (item) {
                        const valueWithInsurance = item.quantity * item.unit_price * 1.1;
                        totalValue  += valueWithInsurance;
                        totalWeight += item.quantity;
                        totalQty    += item.quantity;
                    });
                });

                $('input[name="total_value_with_insurance"]').val(totalValue);
                $('input[name="total_weight"]').val(totalWeight);
                $('input[name="total_quantity"]').val(totalQty);
            }

            function recalcUsedPerProduct() {
                $('#products-table tbody tr').each(function () {
                    $(this).data('used', 0);
                });

                $('tr.loadingPlaceTr').each(function () {
                    let itemsJson = $(this).attr('data-items-json') || '[]';
                    let items;
                    try {
                        items = JSON.parse(itemsJson);
                    } catch (e) {
                        items = [];
                    }

                    items.forEach(function (item) {
                        const $tr = $('#products-table tbody tr[data-product-id="'+ item.product_id +'"]');
                        if (!$tr.length) return;

                        const prev = parseFloat($tr.data('used') || 0);
                        $tr.data('used', prev + parseFloat(item.quantity));
                    });
                });
            }

            // مدیریت فایل‌ها برای هر آدرس
            $(document).on('click', '.btn-add-file-row', function () {
                const loadingIdx = $(this).data('loading-index');
                const $container = $('#loading-files-container-' + loadingIdx);
                addFileRow($container, loadingIdx);
            });

            $(document).on('click', '.btn-remove-file-row', function () {
                $(this).closest('.file-row').remove();
            });

            function addFileRow($container, loadingIdx) {
                const index = Date.now() + '_' + Math.floor(Math.random()*1000);
                const $template = $('#file-input-template .file-row').clone();

                $template.find('.file-input')
                    .attr('name', 'loadings['+ loadingIdx +'][files]['+ index +'][file]');
                $template.find('.file-description-input')
                    .attr('name', 'loadings['+ loadingIdx +'][files]['+ index +'][title]');

                $container.append($template);
            }

            // =========================================================
            //        Step 4: ماشین‌ها و واگن‌ها + آیتم‌ها
            // =========================================================

            // === بخش ماشین‌ها ===
            let truckIndex = $('#trucks-container .truck-row').length || 1;

            // این تابع از template مخفی استفاده می‌کند (truck-row-template)
            function addTruckRow() {
                const idx = truckIndex++;
                let tpl = $('#truck-row-template').html();
                tpl = tpl.replace(/__INDEX__/g, idx);
                const $row = $(tpl);
                $('#trucks-container').append($row);
                initVehicleDatepickers($row);
            }

            $('#btn-add-truck').on('click', function () {
                addTruckRow();
            });

            $(document).on('click', '.btn-remove-truck', function () {
                const $rows = $('#trucks-container .truck-row');
                if ($rows.length <= 1) {
                    alert('حداقل یک ماشین باید ثبت شود.');
                    return;
                }
                $(this).closest('.truck-row').remove();
            });

            // === بخش واگن‌ها ===
            let wagonIndex = $('#wagons-container .wagon-row').length || 0;

            function addWagonRow() {
                const idx = wagonIndex++;
                let tpl = $('#wagon-row-template').html();
                tpl = tpl.replace(/__INDEX__/g, idx);
                const $row = $(tpl);
                $('#wagons-container').append($row);
                initVehicleDatepickers($row);
            }

            $('#btn-add-wagon').on('click', function () {
                addWagonRow();
            });

            $(document).on('click', '.btn-remove-wagon', function () {
                $(this).closest('.wagon-row').remove();
            });

            // === انتخاب آیتم‌ها برای هر وسیله ===
            let currentVehicleType = null; // 'truck' یا 'wagon'
            let currentVehicleIndex = null;

            function recalcUsedPerItem() {
                $('#vehicle-items-table tbody tr').each(function () {
                    $(this).data('used', 0);
                });

                $('.truck-row, .wagon-row').each(function () {
                    const $row = $(this);
                    const itemsJson = $row.attr('data-items-json');
                    if (!itemsJson) return;
                    let items;
                    try { items = JSON.parse(itemsJson); } catch(e) { items = []; }

                    items.forEach(function (item) {
                        const selector = '#vehicle-items-table tbody tr[data-loading-id="'+item.loading_id+'"][data-product-id="'+item.product_id+'"]';
                        const $tr = $(selector);
                        if (!$tr.length) return;
                        const prev = parseFloat($tr.data('used') || 0);
                        $tr.data('used', prev + parseFloat(item.quantity));
                    });
                });
            }

            $(document).on('click', '.btn-select-vehicle-items', function () {
                currentVehicleType  = $(this).data('vehicle-type');  // truck / wagon
                currentVehicleIndex = $(this).data('vehicle-index');

                $('#vehicleItemsModalLabel').text(
                    (currentVehicleType === 'truck' ? 'ماشین ' : 'واگن ') + (parseInt(currentVehicleIndex) + 1)
                );

                // ردیف وسیله فعلی
                const rowSelector = currentVehicleType === 'truck'
                    ? '.truck-row[data-index="'+currentVehicleIndex+'"]'
                    : '.wagon-row[data-index="'+currentVehicleIndex+'"]';

                const $vehicleRow = $(rowSelector);

                // ۱) hidden inputهای آیتم‌های این وسیله را بخوان
                let currentItems = [];
                $vehicleRow.find('input[name^="'+currentVehicleType+'s['+currentVehicleIndex+'][items]"]').each(function () {
                    const name = $(this).attr('name'); // مثلا trucks[0][items][1][loading_id]
                    const value = $(this).val();

                    const match = name.match(/\[(\d+)\]\[items\]\[(\d+)\]\[(loading_id|product_id|quantity)\]/);
                    if (!match) return;

                    const itemIndex = match[2];
                    const field     = match[3];

                    if (!currentItems[itemIndex]) {
                        currentItems[itemIndex] = {};
                    }
                    currentItems[itemIndex][field] = value;
                });

                // ۲) جدول مدال را reset کن
                $('#vehicle-items-table tbody tr').each(function () {
                    const $tr  = $(this);
                    const $chk = $tr.find('.vehicle-item-checkbox');
                    const $qty = $tr.find('.vehicle-item-qty');

                    $chk.prop('checked', false);
                    $qty.val('');
                    $tr.removeClass('table-secondary');
                    $chk.prop('disabled', false);
                    $qty.prop('disabled', false);
                });

                // ۳) انتخاب‌های قبلی این وسیله را روی جدول اعمال کن
                currentItems.forEach(function (item) {
                    if (!item) return;

                    const selector = '#vehicle-items-table tbody tr'
                        + '[data-loading-id="'+item.loading_id+'"]'
                        + '[data-product-id="'+item.product_id+'"]';

                    const $tr  = $(selector);
                    if (!$tr.length) return;

                    const $chk = $tr.find('.vehicle-item-checkbox');
                    const $qty = $tr.find('.vehicle-item-qty');

                    $chk.prop('checked', true);
                    $qty.val(item.quantity);
                });

                const modal = new bootstrap.Modal(document.getElementById('vehicleItemsModal'));
                modal.show();
            });

            $('#btn-save-vehicle-items').on('click', function () {
                if (currentVehicleType === null) return;

                let selectedItems = [];
                let hasError = false;

                $('#vehicle-items-table tbody tr').each(function () {
                    const $tr = $(this);
                    const $chk = $tr.find('.vehicle-item-checkbox');
                    const $qty = $tr.find('.vehicle-item-qty');

                    if (!$chk.is(':checked') || $chk.is(':disabled')) return;

                    const loadingId = $tr.data('loading-id');
                    const productId = $tr.data('product-id');
                    const unit      = $tr.data('unit');
                    const available = parseFloat($tr.data('available-qty'));
                    const used      = parseFloat($tr.data('used') || 0);

                    const qtyVal = parseFloat($qty.val() || 0);
                    if (!qtyVal || qtyVal <= 0) {
                        alert('برای آیتم‌های انتخاب شده مقدار معتبر وارد کنید.');
                        hasError = true;
                        return false;
                    }

                    if (qtyVal > available - used) {
                        alert('مقدار انتخابی برای برخی آیتم‌ها از مقدار باقی‌مانده بیشتر است.');
                        hasError = true;
                        return false;
                    }

                    selectedItems.push({
                        loading_id: loadingId,
                        product_id: productId,
                        unit:       unit,
                        quantity:   qtyVal
                    });
                });

                if (hasError) return;

                const rowSelector = currentVehicleType === 'truck'
                    ? '.truck-row[data-index="'+currentVehicleIndex+'"]'
                    : '.wagon-row[data-index="'+currentVehicleIndex+'"]';
                const $vehicleRow = $(rowSelector);
                const containerId = currentVehicleType === 'truck'
                    ? '#vehicle-items-container-truck-'+currentVehicleIndex
                    : '#vehicle-items-container-wagon-'+currentVehicleIndex;

                $vehicleRow.attr('data-items-json', JSON.stringify(selectedItems || []));
                const $container = $(containerId);
                $container.empty();

                if (!selectedItems.length) {
                    $container.append('<small class="text-muted d-block mb-1">آیتمی انتخاب نشده است.</small>');
                } else {
                    selectedItems.forEach(function (item, i) {
                        const base = currentVehicleType === 'truck'
                            ? 'trucks['+currentVehicleIndex+'][items]['+i+']'
                            : 'wagons['+currentVehicleIndex+'][items]['+i+']';

                        const html = `
                        <div class="small mb-1">
                            <span class="badge bg-light text-dark">
                                لودینگ ${item.loading_id} | محصول ${item.product_id} | مقدار: ${item.quantity} ${item.unit}
                            </span>
                            <input type="hidden" name="${base}[loading_id]" value="${item.loading_id}">
                            <input type="hidden" name="${base}[product_id]" value="${item.product_id}">
                            <input type="hidden" name="${base}[unit]" value="${item.unit}">
                            <input type="hidden" name="${base}[quantity]" value="${item.quantity}">
                        </div>`;
                        $container.append(html);
                    });
                }

                recalcUsedPerItem();

                const modalEl = document.getElementById('vehicleItemsModal');
                const modal    = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            console.log('vehicle accounting JS ready');

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-vehicle-action-btn');
                if (!btn) return;

                const actionType = btn.dataset.actionType; // approve | reject | settle
                const vehicleId  = btn.dataset.vehicleId;
                if (!actionType || !vehicleId) {
                    console.warn('missing data-action-type or data-vehicle-id on button');
                    return;
                }

                const card = btn.closest('.truck-row, .wagon-row');
                if (!card) {
                    console.warn('no vehicle card for button');
                    return;
                }

                const isTruck = card.classList.contains('truck-row');
                const prefix  = isTruck ? 'truck' : 'wagon';

                const transportId = {{ $transport->id }};
                let url = '';
                if (actionType === 'approve') {
                    url = `/transports/${transportId}/vehicles/${vehicleId}/accounting-approve`;
                } else if (actionType === 'reject') {
                    url = `/transports/${transportId}/vehicles/${vehicleId}/accounting-reject`;
                } else if (actionType === 'settle') {
                    url = `/transports/${transportId}/vehicles/${vehicleId}/settle`;
                }

                if (!url) {
                    console.warn('no url for action type', actionType);
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                if (actionType === 'reject') {
                    const reasonInput = card.querySelector(`input[name^="${prefix}s["][name$="[freight_reject_reason]"]`);
                    const reason = reasonInput ? reasonInput.value : '';
                    formData.append('reason', reason);
                }

                if (actionType === 'settle') {
                    const paidAtInput = card.querySelector(`input[name^="${prefix}s["][name$="[freight_paid_at]"]`);
                    const paidAt = paidAtInput ? paidAtInput.value : '';
                    formData.append('paid_at', paidAt);
                }

                console.log('sending', actionType, 'for vehicle', vehicleId, 'to', url);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                    .then(response => {
                        console.log('status', response.status);
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('json', data);

                        const statusContainer = card.querySelector('.vehicle-accounting-status');
                        if (statusContainer && data.freight_status) {
                            let html = '<strong>وضعیت حسابداری کرایه:</strong> ';
                            if (data.freight_status === 'pending') {
                                html += '<span class="badge bg-warning text-dark">در انتظار تأیید حسابداری</span>';
                            } else if (data.freight_status === 'approved') {
                                html += '<span class="badge bg-success">تأیید شده</span>';
                            } else if (data.freight_status === 'rejected') {
                                html += '<span class="badge bg-danger">رد شده</span>';
                                if (data.reject_reason) {
                                    html += '<small class="text-muted d-block">علت: ' + data.reject_reason + '</small>';
                                }
                            }
                            statusContainer.innerHTML = html;
                        }

                        // پیدا کردن دکمه‌ها و ورودی‌های این کارت
                        const approveBtn  = card.querySelector('.js-vehicle-action-btn[data-action-type="approve"]');
                        const rejectBtn   = card.querySelector('.js-vehicle-action-btn[data-action-type="reject"]');
                        const rejectInput = card.querySelector('input[name^="' + prefix + 's["][name$="[freight_reject_reason]"]');
                        const paidAtInput = card.querySelector('input[name^="' + prefix + 's["][name$="[freight_paid_at]"]');
                        const settleWrapper = paidAtInput ? paidAtInput.closest('.d-inline-block') : null;

                        if (data.freight_status === 'approved') {
                            // فقط تسویه بماند
                            if (approveBtn) approveBtn.remove();
                            if (rejectBtn)  rejectBtn.remove();
                            if (rejectInput) rejectInput.remove();
                            // settleWrapper بماند
                        } else if (data.freight_status === 'rejected') {
                            // هیچ‌کدام از دکمه‌ها/تسویه نماند
                            if (approveBtn) approveBtn.remove();
                            if (rejectBtn)  rejectBtn.remove();
                            if (rejectInput) rejectInput.remove();
                            if (settleWrapper) settleWrapper.remove();
                        }

                        if (actionType === 'approve') {
                            btn.disabled = true;
                        }
                    })
                    .catch(err => {
                        console.error('vehicle ajax error', err);
                        alert('در انجام عملیات خطایی رخ داد.');
                    });
            });

            
            document.addEventListener('click', function (e) {
                // افزودن فایل ماشین
                const addTruckFileBtn = e.target.closest('.btn-add-truck-file');
                if (addTruckFileBtn) {
                    const idx = addTruckFileBtn.dataset.index;
                    const container = addTruckFileBtn.closest('.truck-row').querySelector('.truck-files-container');
                    const rows = container.querySelectorAll('.truck-file-row');
                    const nextIndex = rows.length; // 0,1,2,...

                    const row = document.createElement('div');
                    row.className = 'row g-2 mb-2 truck-file-row';
                    row.innerHTML = `
                        <div class="col-md-4">
                            <input type="text"
                                name="trucks[${idx}][files][${nextIndex}][title]"
                                class="form-control"
                                placeholder="عنوان فایل">
                        </div>
                        <div class="col-md-6">
                            <input type="file"
                                name="trucks[${idx}][files][${nextIndex}][file]"
                                class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-remove-truck-file">
                                ×
                            </button>
                        </div>
                    `;
                    container.appendChild(row);
                }

                // افزودن فایل واگن
                const addWagonFileBtn = e.target.closest('.btn-add-wagon-file');
                if (addWagonFileBtn) {
                    const idx = addWagonFileBtn.dataset.index;
                    const container = addWagonFileBtn.closest('.wagon-row').querySelector('.wagon-files-container');
                    const rows = container.querySelectorAll('.wagon-file-row');
                    const nextIndex = rows.length;

                    const row = document.createElement('div');
                    row.className = 'row g-2 mb-2 wagon-file-row';
                    row.innerHTML = `
                        <div class="col-md-4">
                            <input type="text"
                                name="wagons[${idx}][files][${nextIndex}][title]"
                                class="form-control"
                                placeholder="عنوان فایل">
                        </div>
                        <div class="col-md-6">
                            <input type="file"
                                name="wagons[${idx}][files][${nextIndex}][file]"
                                class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-remove-wagon-file">
                                ×
                            </button>
                        </div>
                    `;
                    container.appendChild(row);
                }

                // حذف ردیف فایل ماشین
                const removeTruckFileBtn = e.target.closest('.btn-remove-truck-file');
                if (removeTruckFileBtn) {
                    const row = removeTruckFileBtn.closest('.truck-file-row');
                    if (row) row.remove();
                }

                // حذف ردیف فایل واگن
                const removeWagonFileBtn = e.target.closest('.btn-remove-wagon-file');
                if (removeWagonFileBtn) {
                    const row = removeWagonFileBtn.closest('.wagon-file-row');
                    if (row) row.remove();
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            // delegation روی document (یا یک کانتینر خاص مثل #accounting-step)
            document.addEventListener('click', function (event) {
                var btn = event.target.closest('.js-show-reject-reason');
                if (!btn) return;

                var id = btn.getAttribute('data-vehicle-id');
                var wrapper = document.getElementById('reject-wrapper-' + id);
                if (wrapper) {
                    wrapper.classList.remove('d-none');
                }
            });
        });

    </script>


@endsection


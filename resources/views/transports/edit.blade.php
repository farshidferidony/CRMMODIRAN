{{-- resources/views/transports/edit.blade.php --}}
@extends('layouts.master')

@section('title', 'فرم حمل #' . $transport->id)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4>
                فرم حمل شماره {{ $transport->id }}
                @if($transport->preInvoice)
                    – پیش‌فاکتور شماره {{ $transport->preInvoice->id }}
                @endif
            </h4>

            <a href="{{ route('pre_invoices.transports.index', $transport->pre_invoice_id) }}"
               class="btn btn-secondary btn-sm">
                بازگشت به لیست فرم‌های حمل
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success small">{{ session('success') }}</div>
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

    {{-- فرم اصلی کارشناس فروش --}}
    <form method="POST" action="{{ route('transports.update', $transport->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- ستون اصلی فرم --}}
            <div class="col-lg-8">

                {{-- بخش اول: تنظیمات حمل --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>بخش اول: تنظیمات حمل</strong>
                    </div>
                    <div class="card-body">

                        {{-- تایید تخلیه (دارد / ندارد) --}}
                        <div class="mb-3">
                            <label class="form-label d-block">* تایید تخلیه</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       type="radio"
                                       name="unloading_confirmed"
                                       id="unloading_confirmed_yes"
                                       value="1"
                                       {{ old('unloading_confirmed', $transport->unloading_confirmed) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="unloading_confirmed_yes">دارد</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       type="radio"
                                       name="unloading_confirmed"
                                       id="unloading_confirmed_no"
                                       value="0"
                                       {{ old('unloading_confirmed', $transport->unloading_confirmed) === 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="unloading_confirmed_no">ندارد</label>
                            </div>
                        </div>

                        {{-- نوع حمل (درون شهری / برون شهری) --}}
                        <div class="mb-3">
                            <label class="form-label">* نوع حمل</label>
                            <select name="shipping_type" id="shipping_type" class="form-control" required>
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
                        </div>

                        {{-- نوع انتقال (تک مرحله‌ای / دو مرحله‌ای) --}}
                        <div class="mb-3">
                            <label class="form-label">* نوع انتقال</label>
                            <select name="transfer_type" id="transfer_type" class="form-control" required>
                                <option value="">انتخاب کنید...</option>
                                <option value="single_stage"
                                    {{ old('transfer_type', $transport->transfer_type) === 'single_stage' ? 'selected' : '' }}>
                                    تک مرحله‌ای
                                </option>
                                <option value="two_stage"
                                    {{ old('transfer_type', $transport->transfer_type) === 'two_stage' ? 'selected' : '' }}>
                                    دو مرحله‌ای
                                </option>
                            </select>
                        </div>

                        {{-- نوع ماشین درخواستی --}}
                        <div class="mb-3">
                            <label class="form-label">* نوع ماشین درخواستی</label>
                            <select name="requested_truck_type" id="requested_truck_type" class="form-control" required>
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
                        </div>

                        {{-- نوع واگن (فقط اگر دو مرحله‌ای) --}}
                        @php
                            $transferType = old('transfer_type', $transport->transfer_type);
                        @endphp
                        <div class="mb-3" id="wagon_type_wrapper"
                             style="{{ $transferType === 'two_stage' ? '' : 'display:none;' }}">
                            <label class="form-label">نوع واگن</label>
                            <select name="requested_wagon_type" id="requested_wagon_type" class="form-control">
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
                            <small class="text-muted">
                                در صورت انتخاب انتقال دو مرحله‌ای، تکمیل نوع واگن الزامی است.
                            </small>
                        </div>

                    </div>
                </div>

                {{-- بخش دوم: اطلاعات فرستنده --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>بخش دوم: اطلاعات فرستنده</strong>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">* نام فرستنده</label>
                            <input type="text"
                                   name="sender_name"
                                   class="form-control"
                                   value="{{ old('sender_name', $transport->sender_name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* کد پستی فرستنده</label>
                            <input type="text"
                                   name="sender_postal_code"
                                   class="form-control"
                                   value="{{ old('sender_postal_code', $transport->sender_postal_code) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* کد ملی فرستنده</label>
                            <input type="text"
                                   name="sender_national_code"
                                   class="form-control"
                                   value="{{ old('sender_national_code', $transport->sender_national_code) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* تلفن فرستنده</label>
                            <input type="text"
                                   name="sender_phone"
                                   class="form-control"
                                   value="{{ old('sender_phone', $transport->sender_phone) }}"
                                   required>
                        </div>
                    </div>
                </div>
                {{-- بخش سوم: اطلاعات گیرنده و محل تخلیه --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>بخش سوم: اطلاعات گیرنده و محل تخلیه</strong>
                    </div>
                    <div class="card-body">

                        @php
                            $isForeign = isset($customerScope) && $customerScope === 'foreign';
                        @endphp

                        @if(isset($customerScope))
                            <div class="alert alert-light border small mb-3">
                                <strong>نوع مشتری:</strong>
                                @if($isCompany ?? false)
                                    شرکت
                                @elseif($isPerson ?? false)
                                    شخص حقیقی
                                @else
                                    نامشخص
                                @endif
                                –
                                <strong>محدوده:</strong>
                                {{ $customerScope === 'domestic' ? 'داخلی' : 'خارجی' }}

                                @if($isForeign && ($countryName || $provinceName || $cityName))
                                    <br>
                                    <strong>موقعیت:</strong>
                                    {{ $countryName ?? '-' }}
                                    @if($provinceName) - {{ $provinceName }} @endif
                                    @if($cityName) - {{ $cityName }} @endif
                                @endif
                            </div>
                        @endif

                        {{-- اطلاعات اصلی گیرنده --}}
                        <div class="mb-3">
                            <label class="form-label">* نام شرکت (در صورت داشتن شخصیت حقوقی)</label>
                            <input type="text"
                                   name="receiver_company"
                                   class="form-control @error('receiver_company') is-invalid @enderror"
                                   value="{{ old('receiver_company', $transport->receiver_company) }}"
                                   required>
                            @error('receiver_company')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* نام شخص (گیرنده / مسئول خرید)</label>
                            <input type="text"
                                   name="receiver_name"
                                   class="form-control @error('receiver_name') is-invalid @enderror"
                                   value="{{ old('receiver_name', $transport->receiver_name) }}"
                                   required>
                            @error('receiver_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* کد پستی گیرنده</label>
                            <input type="text"
                                   name="receiver_postal_code"
                                   class="form-control @error('receiver_postal_code') is-invalid @enderror"
                                   value="{{ old('receiver_postal_code', $transport->receiver_postal_code) }}"
                                   required>
                            @error('receiver_postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- شناسه داخلی / کد ملی گیرنده --}}
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $isForeign ? 'شناسه داخلی (در صورت وجود)' : '* کد ملی گیرنده' }}
                            </label>
                            <input type="text"
                                   name="receiver_national_code"
                                   class="form-control @error('receiver_national_code') is-invalid @enderror"
                                   value="{{ old('receiver_national_code', $transport->receiver_national_code) }}"
                                   {{ $isForeign ? '' : 'required' }}>
                            @error('receiver_national_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if($isForeign)
                                <small class="text-muted">
                                    مشتری خارجی است؛ فقط در صورت داشتن شناسه داخلی، این فیلد را تکمیل کنید.
                                </small>
                            @endif
                        </div>

                        {{-- شماره پاسپورت گیرنده (فقط برای خارجی) --}}
                        @if($isForeign && $person ?? false)
                            <div class="mb-3">
                                <label class="form-label">شماره پاسپورت گیرنده (در صورت نیاز)</label>
                                <input type="text"
                                       name="receiver_passport_number"
                                       class="form-control"
                                       value="{{ old('receiver_passport_number', $person->passport_number) }}">
                                <small class="text-muted">
                                    این فیلد فقط برای مقاصدی استفاده می‌شود که به شماره پاسپورت گیرنده نیاز دارند.
                                </small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">تلفن گیرنده</label>
                            <input type="text"
                                   name="receiver_phone"
                                   class="form-control @error('receiver_phone') is-invalid @enderror"
                                   value="{{ old('receiver_phone', $transport->receiver_phone) }}">
                            @error('receiver_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* موبایل گیرنده</label>
                            <input type="text"
                                   name="receiver_mobile"
                                   class="form-control @error('receiver_mobile') is-invalid @enderror"
                                   value="{{ old('receiver_mobile', $transport->receiver_mobile) }}"
                                   required>
                            @error('receiver_mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">* آدرس محل فعالیت</label>
                            <textarea name="receiver_activity_address"
                                      class="form-control @error('receiver_activity_address') is-invalid @enderror"
                                      rows="2"
                                      required>{{ old('receiver_activity_address', $transport->receiver_activity_address) }}</textarea>
                            @error('receiver_activity_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if($isForeign)
                                <small class="text-muted">
                                    برای مشتری خارجی، آدرس محل فعالیت را به‌صورت دقیق و در صورت نیاز به لاتین وارد کنید.
                                </small>
                            @endif
                        </div>

                        @php
                            $unloadingApproved = old('unloading_place_approved', $transport->unloading_place_approved);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label d-block">* محل تخلیه مورد تایید است؟</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input @error('unloading_place_approved') is-invalid @enderror"
                                       type="radio"
                                       name="unloading_place_approved"
                                       id="unloading_place_approved_yes"
                                       value="1"
                                       {{ (string)$unloadingApproved === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="unloading_place_approved_yes">بله</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input @error('unloading_place_approved') is-invalid @enderror"
                                       type="radio"
                                       name="unloading_place_approved"
                                       id="unloading_place_approved_no"
                                       value="0"
                                       {{ (string)$unloadingApproved === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="unloading_place_approved_no">خیر</label>
                            </div>
                            @error('unloading_place_approved')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="unloading_extra_wrapper" style="{{ (string)$unloadingApproved === '1' ? 'display:none;' : 'display:block;' }}">
                            <div class="mb-3">
                                <label class="form-label">* آدرس محل تخلیه</label>
                                <textarea name="unloading_address"
                                          class="form-control @error('unloading_address') is-invalid @enderror"
                                          rows="2">{{ old('unloading_address', $transport->unloading_address) }}</textarea>
                                @error('unloading_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">* کد پستی محل تخلیه</label>
                                <input type="text"
                                       name="unloading_postal_code"
                                       class="form-control @error('unloading_postal_code') is-invalid @enderror"
                                       value="{{ old('unloading_postal_code', $transport->unloading_postal_code) }}">
                                @error('unloading_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">* مسئول تخلیه / انباردار</label>
                                <input type="text"
                                       name="unloading_responsible"
                                       class="form-control @error('unloading_responsible') is-invalid @enderror"
                                       value="{{ old('unloading_responsible', $transport->unloading_responsible) }}">
                                @error('unloading_responsible')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">* شماره تماس مسئول تخلیه</label>
                                <input type="text"
                                       name="unloading_responsible_phone"
                                       class="form-control @error('unloading_responsible_phone') is-invalid @enderror"
                                       value="{{ old('unloading_responsible_phone', $transport->unloading_responsible_phone) }}">
                                @error('unloading_responsible_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- بخش چهارم: تیک تایید کارشناس فروش --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>بخش چهارم: تایید کارشناس فروش</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="approve_sales_expert"
                                   id="approve_sales_expert"
                                   value="1"
                                   {{ old('approve_sales_expert', $transport->approved_by_sales_expert) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approve_sales_expert">
                                تایید می‌کنم که اطلاعات فوق صحیح است و فرم حمل برای ادامه مراحل ارسال شود.
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            با زدن این تیک، فرم حمل به کارشناسان خرید و سپس لجستیک ارجاع داده می‌شود.
                        </small>
                    </div>
                </div>

                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-primary">
                        ذخیره فرم حمل
                    </button>
                </div>

            </div>
            {{-- ستون کناری: اطلاعات خلاصه پیش‌فاکتور --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>خلاصه پیش‌فاکتور / مشتری</strong>
                    </div>
                    <div class="card-body small">
                        @if($transport->preInvoice)
                            <p class="mb-1">
                                <strong>شماره پیش‌فاکتور:</strong>
                                {{ $transport->preInvoice->id }}
                            </p>
                            <p class="mb-1">
                                <strong>مشتری:</strong>
                                {{ $transport->preInvoice->customer->display_name ?? '-' }}
                            </p>
                            <p class="mb-1">
                                <strong>محدوده مشتری:</strong>
                                @if(!empty($customerScope))
                                    {{ $customerScope === 'domestic' ? 'داخلی' : 'خارجی' }}
                                @else
                                    -
                                @endif
                            </p>
                            @if($isForeign ?? false)
                                <p class="mb-1">
                                    <strong>کشور / استان / شهر:</strong>
                                    {{ $countryName ?? '-' }}
                                    @if($provinceName) - {{ $provinceName }} @endif
                                    @if($cityName) - {{ $cityName }} @endif
                                </p>
                            @endif
                            <p class="mb-1">
                                <strong>مبلغ کل پیش‌فاکتور:</strong>
                                {{ number_format($transport->preInvoice->total_amount) }}
                            </p>
                            <p class="mb-0 text-muted">
                                از این پیش‌فاکتور، فرم حمل در چند مرحله تکمیل خواهد شد (فروش، خرید، لجستیک، حسابداری، مدیر فروش).
                            </p>
                        @else
                            <p class="mb-0 text-muted">این فرم حمل به پیش‌فاکتور متصل نیست.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    function toggleWagonType() {
        const transferType = document.getElementById('transfer_type').value;
        const wrapper = document.getElementById('wagon_type_wrapper');
        if (transferType === 'two_stage') {
            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
            document.getElementById('requested_wagon_type').value = '';
        }
    }

    function toggleUnloadingExtra() {
        const yes = document.getElementById('unloading_place_approved_yes');
        const wrapper = document.getElementById('unloading_extra_wrapper');
        if (yes && yes.checked) {
            wrapper.style.display = 'none';
        } else {
            wrapper.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // تنظیم اولیه
        toggleWagonType();
        toggleUnloadingExtra();

        document.getElementById('transfer_type').addEventListener('change', toggleWagonType);

        const unloadingYes = document.getElementById('unloading_place_approved_yes');
        const unloadingNo  = document.getElementById('unloading_place_approved_no');

        if (unloadingYes) unloadingYes.addEventListener('change', toggleUnloadingExtra);
        if (unloadingNo)  unloadingNo.addEventListener('change', toggleUnloadingExtra);
    });
</script>
@endsection

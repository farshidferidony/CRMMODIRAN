@extends('layouts.master')

@section('title')
    ویرایش مشتری یا شرکت
@endsection

@section('css')
    <link href="{{ URL::asset('/assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت طرف حساب @endslot
    @slot('title') ویرایش مشتری/شرکت @endslot
@endcomponent

@php
    use App\Models\ProvinceFaEn;
    use App\Models\CityFaEn;

    $person       = $customer->persons->first();
    $companyModel = $customer->companies->first();
@endphp

<div class="row">
    <div class="col-lg-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.update', $customer->id) }}" id="multiForm">
            @csrf
            @method('PUT')

            @php
                // دقیقاً مشابه create، فقط پیش‌فرض را از مدل می‌گیریم
                $scope  = old('customer_scope', $customer->customer_scope ?? 'domestic');
                $source = old('source', $customer->source ?? '');
                $active = old('is_active', $customer->is_active ?? 1);
            @endphp

            {{-- اطلاعات کلی مشتری (Customer) --}}
            <div class="card mb-3">
                <div class="card-body row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">نوع مشتری</label>
                        <select name="customer_scope" id="customer_scope" class="form-select">
                            <option value="domestic" {{ $scope === 'domestic' ? 'selected' : '' }}>داخلی</option>
                            <option value="foreign"  {{ $scope === 'foreign'  ? 'selected' : '' }}>خارجی</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            تعیین می‌کند کد ملی/پاسپورت و کد اقتصادی/شماره ثبت چگونه نمایش داده شوند.
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">نوع آشنایی با مشتری</label>
                        <select name="source" class="form-select">
                            <option value="">انتخاب کنید...</option>
                            <option value="website"           {{ $source === 'website' ? 'selected' : '' }}>وبسایت</option>
                            <option value="instagram"         {{ $source === 'instagram' ? 'selected' : '' }}>اینستاگرام</option>
                            <option value="telegram"          {{ $source === 'telegram' ? 'selected' : '' }}>تلگرام</option>
                            <option value="business_partners" {{ $source === 'business_partners' ? 'selected' : '' }}>شرکای تجاری</option>
                            <option value="phone_marketing"   {{ $source === 'phone_marketing' ? 'selected' : '' }}>بازاریابی تلفنی</option>
                            <option value="from_employees"    {{ $source === 'from_employees' ? 'selected' : '' }}>از طریق کارمندان</option>
                            <option value="from_customers"    {{ $source === 'from_customers' ? 'selected' : '' }}>از طریق مشتریان</option>
                            <option value="word_of_mouth"     {{ $source === 'word_of_mouth' ? 'selected' : '' }}>بازاریابی دهان به دهان</option>
                            <option value="public_relations"  {{ $source === 'public_relations' ? 'selected' : '' }}>روابط عمومی</option>
                            <option value="seminar"           {{ $source === 'seminar' ? 'selected' : '' }}>سمینار</option>
                            <option value="conference"        {{ $source === 'conference' ? 'selected' : '' }}>همایش</option>
                            <option value="exhibition"        {{ $source === 'exhibition' ? 'selected' : '' }}>نمایشگاه</option>
                            <option value="mass_advertising"  {{ $source === 'mass_advertising' ? 'selected' : '' }}>تبلیغات انبوه</option>
                            <option value="email_marketing"   {{ $source === 'email_marketing' ? 'selected' : '' }}>ایمیل مارکتینگ</option>
                            <option value="sms_marketing"     {{ $source === 'sms_marketing' ? 'selected' : '' }}>اس‌ام‌اس مارکتینگ</option>
                            <option value="fax_marketing"     {{ $source === 'fax_marketing' ? 'selected' : '' }}>فکس مارکتینگ</option>
                            <option value="direct_contact"    {{ $source === 'direct_contact' ? 'selected' : '' }}>ارتباط مستقیم</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   value="1" {{ $active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">مشتری فعال است</label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addcustomer-accordion" class="custom-accordion">

                {{-- شخص حقیقی --}}
                <div class="card">
                    <a href="#person-collapse" class="text-dark" data-bs-toggle="collapse"
                       aria-expanded="true" aria-controls="person-collapse">
                        <div class="p-4 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">01</div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="font-size-16 mb-1">مشخصات مشتری حقیقی</h5>
                                <p class="text-muted mb-0">مشخصات و تماس شخص</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                            </div>
                        </div>
                    </a>
                    <div id="person-collapse" class="collapse show" data-bs-parent="#addcustomer-accordion">
                        <div class="p-4 border-top">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">نام</label>
                                    <input type="text" name="person[first_name]"
                                           class="form-control @error('person.first_name') is-invalid @enderror"
                                           value="{{ old('person.first_name', $person->first_name ?? '') }}">
                                    @error('person.first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">نام خانوادگی</label>
                                    <input type="text" name="person[last_name]"
                                           class="form-control @error('person.last_name') is-invalid @enderror"
                                           value="{{ old('person.last_name', $person->last_name ?? '') }}">
                                    @error('person.last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">تاریخ تولد</label>
                                    <input type="date" name="person[birthdate]"
                                           class="form-control @error('person.birthdate') is-invalid @enderror"
                                           value="{{ old('person.birthdate', $person->birthdate ?? '') }}">
                                    @error('person.birthdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- داخلی/خارجی: کد ملی یا پاسپورت --}}
                            <div class="row" id="person_national_passport_row">
                                <div class="col-md-4 mb-3 person-national-wrapper" style="{{ $scope === 'foreign' ? 'display:none;' : '' }}">
                                    <label class="form-label">کد ملی</label>
                                    <input type="text" name="person[national_code]"
                                           class="form-control @error('person.national_code') is-invalid @enderror"
                                           value="{{ old('person.national_code', $person->national_code ?? '') }}">
                                    @error('person.national_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3 person-passport-wrapper" style="{{ $scope === 'domestic' ? 'display:none;' : '' }}">
                                    <label class="form-label">شماره پاسپورت</label>
                                    <input type="text" name="person[passport_number]"
                                           class="form-control @error('person.passport_number') is-invalid @enderror"
                                           value="{{ old('person.passport_number', $person->passport_number ?? '') }}">
                                    @error('person.passport_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ایمیل</label>
                                    <input type="email" name="person[email]"
                                           class="form-control @error('person.email') is-invalid @enderror"
                                           value="{{ old('person.email', $person->email ?? '') }}">
                                    @error('person.email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- آدرس‌ها و شماره تماس شخص --}}
                            <h5 class="mt-4">آدرس‌ها و شماره تماس شخص</h5>
                            @php
                                $personAddresses = old('person.addresses', []);
                                if (empty($personAddresses) && $person && $person->addresses) {
                                    $personAddresses = $person->addresses->map(function($addr) {
                                        $provinceOptions = [];
                                        $cityOptions     = [];

                                        if ($addr->country_id) {
                                            $provinceOptions = ProvinceFaEn::where('country_id', $addr->country_id)
                                                ->where('status_province', 1)
                                                ->orderBy('name_fa')
                                                ->get();
                                        }
                                        if ($addr->province_id) {
                                            $cityOptions = CityFaEn::where('province_id', $addr->province_id)
                                                ->where('status_city', 1)
                                                ->orderBy('name_fa')
                                                ->get();
                                        }

                                        return [
                                            'id'               => $addr->id,
                                            'country_id'       => $addr->country_id,
                                            'province_id'      => $addr->province_id,
                                            'city_id'          => $addr->city_id,
                                            'postal_code'      => $addr->postal_code,
                                            'address_detail'   => $addr->address_detail,
                                            'province_options' => $provinceOptions,
                                            'city_options'     => $cityOptions,
                                            'contacts'         => $addr->contacts->map(fn($c) => [
                                                'id'    => $c->id,
                                                'type'  => $c->type,
                                                'value' => $c->value,
                                            ])->toArray(),
                                        ];
                                    })->toArray();
                                }
                            @endphp

                            <div id="person-addresses">
                                @forelse($personAddresses as $pIdx => $address)
                                    @include('customers.partials.address_contact', [
                                        'prefix'       => 'person',
                                        'addressIndex' => $pIdx,
                                        'addressOld'   => $address,
                                        'countries'    => $countries,
                                    ])
                                @empty
                                    @include('customers.partials.address_contact', [
                                        'prefix'       => 'person',
                                        'addressIndex' => 0,
                                        'addressOld'   => [],
                                        'countries'    => $countries,
                                    ])
                                @endforelse
                            </div>

                            <button type="button" class="btn btn-link" onclick="addAddressRow('person')">
                                افزودن آدرس جدید برای شخص
                            </button>
                        </div>
                    </div>
                </div>

                {{-- شرکت حقوقی --}}
                <div class="card">
                    <a href="#company-collapse" class="text-dark collapsed" data-bs-toggle="collapse"
                       aria-controls="company-collapse">
                        <div class="p-4 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">02</div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="font-size-16 mb-1">مشخصات شرکت حقوقی</h5>
                                <p class="text-muted mb-0">مشخصات و تماس شرکت</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                            </div>
                        </div>
                    </a>
                    <div id="company-collapse" class="collapse" data-bs-parent="#addcustomer-accordion">
                        <div class="p-4 border-top">
                            @php
                                $company = $companyModel;
                            @endphp

                            <div class="mb-3">
                                <label class="form-label">نام شرکت</label>
                                <input type="text" name="company[name]"
                                       class="form-control @error('company.name') is-invalid @enderror"
                                       value="{{ old('company.name', $company->name ?? '') }}">
                                @error('company.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- داخلی/خارجی: کد اقتصادی و شماره ثبت --}}
                            <div class="row" id="company_codes_row">
                                <div class="col-md-4 mb-3 company-economic-wrapper" style="{{ $scope === 'foreign' ? 'display:none;' : '' }}">
                                    <label class="form-label">کد اقتصادی</label>
                                    <input type="text" name="company[economic_code]"
                                           class="form-control @error('company.economic_code') is-invalid @enderror"
                                           value="{{ old('company.economic_code', $company->economic_code ?? '') }}">
                                    @error('company.economic_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3 company-registration-wrapper" style="{{ $scope === 'foreign' ? 'display:none;' : '' }}">
                                    <label class="form-label">شماره ثبت</label>
                                    <input type="text" name="company[registration_number]"
                                           class="form-control @error('company.registration_number') is-invalid @enderror"
                                           value="{{ old('company.registration_number', $company->registration_number ?? '') }}">
                                    @error('company.registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ایمیل شرکت</label>
                                    <input type="email" name="company[email]"
                                           class="form-control @error('company.email') is-invalid @enderror"
                                           value="{{ old('company.email', $company->email ?? '') }}">
                                    @error('company.email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- آدرس‌ها و شماره تماس شرکت --}}
                            <h5 class="mt-4">آدرس‌ها و شماره تماس شرکت</h5>
                            @php
                                $companyAddresses = old('company.addresses', []);
                                if (empty($companyAddresses) && $company && $company->addresses) {
                                    $companyAddresses = $company->addresses->map(function($addr) {
                                        $provinceOptions = [];
                                        $cityOptions     = [];

                                        if ($addr->country_id) {
                                            $provinceOptions = ProvinceFaEn::where('country_id', $addr->country_id)
                                                ->where('status_province', 1)
                                                ->orderBy('name_fa')
                                                ->get();
                                        }
                                        if ($addr->province_id) {
                                            $cityOptions = CityFaEn::where('province_id', $addr->province_id)
                                                ->where('status_city', 1)
                                                ->orderBy('name_fa')
                                                ->get();
                                        }

                                        return [
                                            'id'               => $addr->id,
                                            'country_id'       => $addr->country_id,
                                            'province_id'      => $addr->province_id,
                                            'city_id'          => $addr->city_id,
                                            'postal_code'      => $addr->postal_code,
                                            'address_detail'   => $addr->address_detail,
                                            'province_options' => $provinceOptions,
                                            'city_options'     => $cityOptions,
                                            'contacts'         => $addr->contacts->map(fn($c) => [
                                                'id'    => $c->id,
                                                'type'  => $c->type,
                                                'value' => $c->value,
                                            ])->toArray(),
                                        ];
                                    })->toArray();
                                }
                            @endphp

                            <div id="company-addresses">
                                @forelse($companyAddresses as $cIdx => $address)
                                    @include('customers.partials.address_contact', [
                                        'prefix'       => 'company',
                                        'addressIndex' => $cIdx,
                                        'addressOld'   => $address,
                                        'countries'    => $countries,
                                    ])
                                @empty
                                    @include('customers.partials.address_contact', [
                                        'prefix'       => 'company',
                                        'addressIndex' => 0,
                                        'addressOld'   => [],
                                        'countries'    => $countries,
                                    ])
                                @endforelse
                            </div>

                            <button type="button" class="btn btn-link" onclick="addAddressRow('company')">
                                افزودن آدرس جدید برای شرکت
                            </button>


                            {{-- افزودن افراد قبلی به این شرکت --}}
                            <hr class="my-4">
                            <h5 class="font-size-15 mb-3">افزودن افراد قبلاً ثبت‌شده به این شرکت</h5>

                            @php
                                $company = $companyModel ?? null;

                                $existingRelatedPersons = [];
                                $existingRole = null;

                                if ($company && $company->relationLoaded('persons') && $company->persons && $company->persons->count()) {
                                    $existingRelatedPersons = $company->persons->pluck('id')->toArray();

                                    // اگر حداقل یک شخص وجود دارد، role را از pivot اولین شخص بگیر
                                    $firstPerson = $company->persons->first();
                                    if ($firstPerson && $firstPerson->pivot) {
                                        $existingRole = $firstPerson->pivot->role;
                                    }
                                }

                                $selectedPersons    = old('existing_person_ids', $existingRelatedPersons);
                                $existingPersonRole = old('existing_person_role', $existingRole ?? '');
                            @endphp


                            <div class="mb-3">
                                <label class="form-label">
                                    انتخاب افراد موجود (کارمندان قبلی)
                                </label>
                                <select name="existing_person_ids[]"
                                        class="form-select"
                                        multiple
                                        data-toggle="select2">
                                    @foreach($existingPersons as $p)
                                        <option value="{{ $p->id }}"
                                            {{ in_array($p->id, $selectedPersons ?? []) ? 'selected' : '' }}>
                                            {{ $p->first_name }} {{ $p->last_name }}
                                            @if($p->national_code)
                                                (کد ملی: {{ $p->national_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">
                                    می‌توانید چند شخص قبلی را به عنوان کارمند این شرکت مرتبط کنید.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نقش / رسته شغلی افراد انتخاب‌شده</label>
                                <input type="text"
                                    name="existing_person_role"
                                    class="form-control"
                                    value="{{ $existingPersonRole }}"
                                    placeholder="مثلاً: کارشناس خرید، کارشناس فروش، مدیر خرید و ...">
                            </div>



                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4 mb-0">
                <div class="col text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="uil uil-file-alt me-1"></i> ذخیره تغییرات
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-danger">
                        <i class="uil uil-times me-1"></i> لغو
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/select2/select2.min.js') }}"></script>

    {{-- قالب مخفی آدرس برای افزودن داینامیک --}}
    <div id="address-template" style="display:none;">
        <div class="address-group mb-4 border rounded p-3">
            <input type="hidden" name="__PREFIX__[addresses][__INDEX__][id]" value="">
            <div class="row mb-2">
                <div class="col-md-4 mb-1">
                    <label class="form-label">کشور</label>
                    <select name="__PREFIX__[addresses][__INDEX__][country_id]"
                            class="form-control country-select"
                            data-prefix="__PREFIX__"
                            data-index="__INDEX__">
                        <option value="">انتخاب کنید</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}">
                                {{ app()->getLocale() == 'fa' ? $c->name_fa : $c->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-1">
                    <label class="form-label">استان</label>
                    <select name="__PREFIX__[addresses][__INDEX__][province_id]"
                            class="form-control province-select"
                            data-prefix="__PREFIX__"
                            data-index="__INDEX__">
                        <option value="">انتخاب کنید</option>
                    </select>
                </div>
                <div class="col-md-4 mb-1">
                    <label class="form-label">شهر</label>
                    <select name="__PREFIX__[addresses][__INDEX__][city_id]"
                            class="form-control city-select"
                            data-prefix="__PREFIX__"
                            data-index="__INDEX__">
                        <option value="">انتخاب کنید</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <input type="text"
                       name="__PREFIX__[addresses][__INDEX__][address_detail]"
                       class="form-control"
                       placeholder="آدرس">
            </div>
            <div id="__PREFIX__-contacts-__INDEX__">
                <div class="contact-row row mb-2">
                    <div class="col-md-4">
                        <select name="__PREFIX__[addresses][__INDEX__][contacts][0][type]" class="form-control">
                            <option value="mobile">موبایل</option>
                            <option value="phone">تلفن ثابت</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text"
                               name="__PREFIX__[addresses][__INDEX__][contacts][0][value]"
                               class="form-control"
                               placeholder="شماره">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success btn-sm"
                                onclick="addContactRow('__PREFIX__', __INDEX__)">+</button>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="this.closest('.address-group').remove()">حذف آدرس</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addAddressRow(prefix) {
            let addressesDiv = document.getElementById(prefix + '-addresses');
            let count        = addressesDiv.getElementsByClassName('address-group').length;

            let tpl = document.getElementById('address-template').innerHTML;
            tpl     = tpl.replace(/__PREFIX__/g, prefix)
                         .replace(/__INDEX__/g, count);

            addressesDiv.insertAdjacentHTML('beforeend', tpl);
        }

        function addContactRow(prefix, addressIdx) {
            let contactsId  = prefix + '-contacts-' + addressIdx;
            let contactsDiv = document.getElementById(contactsId);
            let count       = contactsDiv.getElementsByClassName('contact-row').length;

            let html = `
            <div class="contact-row row mb-2">
                <div class="col-md-4">
                    <select name="${prefix}[addresses][${addressIdx}][contacts][${count}][type]" class="form-control">
                        <option value="mobile">موبایل</option>
                        <option value="phone">تلفن ثابت</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text"
                           name="${prefix}[addresses][${addressIdx}][contacts][${count}][value]"
                           class="form-control"
                           placeholder="شماره">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="this.closest('.contact-row').remove()">-</button>
                </div>
            </div>`;
            contactsDiv.insertAdjacentHTML('beforeend', html);
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('country-select')) {
                let select         = e.target;
                let countryId      = select.value;
                let prefix         = select.dataset.prefix;
                let index          = select.dataset.index;
                let provinceSelect = document.querySelector(
                    `select[name="${prefix}[addresses][${index}][province_id]"]`
                );
                let citySelect     = document.querySelector(
                    `select[name="${prefix}[addresses][${index}][city_id]"]`
                );
                provinceSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
                citySelect.innerHTML     = '<option value="">انتخاب کنید</option>';

                if (countryId) {
                    fetch(`{{ route('geo.provinces') }}?country_id=` + countryId)
                        .then(res => res.json())
                        .then(data => {
                            let opts = '<option value="">انتخاب کنید</option>';
                            data.forEach(function(item) {
                                let name = "{{ app()->getLocale() == 'fa' ? ':fa' : ':en' }}"
                                    .replace(':fa', item.name_fa)
                                    .replace(':en', item.name_en);
                                opts += `<option value="${item.id}">${name}</option>`;
                            });
                            provinceSelect.innerHTML = opts;
                        });
                } else {
                    provinceSelect.innerHTML = '<option value="">انتخاب کنید</option>';
                }
            }

            if (e.target.classList.contains('province-select')) {
                let select     = e.target;
                let provinceId = select.value;
                let prefix     = select.dataset.prefix;
                let index      = select.dataset.index;
                let citySelect = document.querySelector(
                    `select[name="${prefix}[addresses][${index}][city_id]"]`
                );
                citySelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

                if (provinceId) {
                    fetch(`{{ route('geo.cities') }}?province_id=` + provinceId)
                        .then(res => res.json())
                        .then(data => {
                            let opts = '<option value="">انتخاب کنید</option>';
                            data.forEach(function(item) {
                                let name = "{{ app()->getLocale() == 'fa' ? ':fa' : ':en' }}"
                                    .replace(':fa', item.name_fa)
                                    .replace(':en', item.name_en);
                                opts += `<option value="${item.id}">${name}</option>`;
                            });
                            citySelect.innerHTML = opts;
                        });
                } else {
                    citySelect.innerHTML = '<option value="">انتخاب کنید</option>';
                }
            }
        });

        // سوییچ داخلی/خارجی دقیقاً مثل create
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('[data-toggle="select2"]').select2({ width: '100%', dir: 'rtl' });
            }

            const scopeSelect = document.getElementById('customer_scope');
            if (!scopeSelect) return;

            scopeSelect.addEventListener('change', function () {
                const scope = this.value;

                // شخص: کد ملی/پاسپورت
                const nationalWrapper = document.querySelector('.person-national-wrapper');
                const passportWrapper = document.querySelector('.person-passport-wrapper');

                if (nationalWrapper && passportWrapper) {
                    if (scope === 'domestic') {
                        nationalWrapper.style.display = 'block';
                        passportWrapper.style.display = 'none';
                        passportWrapper.querySelector('input').value = '';
                    } else {
                        nationalWrapper.style.display = 'none';
                        nationalWrapper.querySelector('input').value = '';
                        passportWrapper.style.display = 'block';
                    }
                }

                // شرکت: کد اقتصادی و شماره ثبت
                const economicWrapper     = document.querySelector('.company-economic-wrapper');
                const registrationWrapper = document.querySelector('.company-registration-wrapper');

                if (economicWrapper && registrationWrapper) {
                    if (scope === 'domestic') {
                        economicWrapper.style.display = 'block';
                        registrationWrapper.style.display = 'block';
                    } else {
                        economicWrapper.style.display = 'none';
                        registrationWrapper.style.display = 'none';
                        economicWrapper.querySelector('input').value = '';
                        registrationWrapper.querySelector('input').value = '';
                    }
                }
            });

            // تریگر اولیه برای هم‌راستایی با مقدار old()/مشتری
            scopeSelect.dispatchEvent(new Event('change'));
        });
    </script>
@endsection

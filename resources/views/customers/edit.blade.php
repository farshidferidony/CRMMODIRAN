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

<div class="row">
    <div class="col-lg-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.update', $customer->id) }}" id="multiForm">
            @csrf
            @method('PUT')

            <div id="addcustomer-accordion" class="custom-accordion">

                {{-- مشتری حقیقی --}}
                <div class="card">
                    <a href="#person-collapse" class="text-dark" data-bs-toggle="collapse" aria-expanded="true" aria-controls="person-collapse">
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
                                           value="{{ old('person.first_name', $customer->first_name) }}">
                                    @error('person.first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">نام خانوادگی</label>
                                    <input type="text" name="person[last_name]"
                                           class="form-control @error('person.last_name') is-invalid @enderror"
                                           value="{{ old('person.last_name', $customer->last_name) }}">
                                    @error('person.last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">تاریخ تولد</label>
                                    <input type="date" name="person[birthdate]"
                                           class="form-control @error('person.birthdate') is-invalid @enderror"
                                           value="{{ old('person.birthdate', $customer->birthdate) }}">
                                    @error('person.birthdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">کد ملی</label>
                                    <input type="text" name="person[national_code]"
                                           class="form-control @error('person.national_code') is-invalid @enderror"
                                           value="{{ old('person.national_code', $customer->national_code) }}">
                                    @error('person.national_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">شماره پاسپورت</label>
                                    <input type="text" name="person[passport_number]"
                                           class="form-control @error('person.passport_number') is-invalid @enderror"
                                           value="{{ old('person.passport_number', $customer->passport_number) }}">
                                    @error('person.passport_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ایمیل</label>
                                    <input type="email" name="person[email]"
                                           class="form-control @error('person.email') is-invalid @enderror"
                                           value="{{ old('person.email', $customer->email) }}">
                                    @error('person.email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- آدرس‌ها و تماس‌های شخص --}}
                            <h5 class="mt-4">آدرس‌ها و شماره تماس شخص</h5>
                            <div id="person-addresses">
                                @php
                                    use App\Models\ProvinceFaEn;
                                    use App\Models\CityFaEn;

                                    $personAddresses = old('person.addresses', []);
                                    if (empty($personAddresses) && $customer->addresses) {
                                        $personAddresses = $customer->addresses->map(function($addr) {
                                            $provinceOptions = [];
                                            $cityOptions = [];

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
                                                'id'              => $addr->id,
                                                'country_id'      => $addr->country_id,
                                                'province_id'     => $addr->province_id,
                                                'city_id'         => $addr->city_id,
                                                'postal_code'     => $addr->postal_code,
                                                'address_detail'  => $addr->address_detail,
                                                'province_options'=> $provinceOptions,
                                                'city_options'    => $cityOptions,
                                                'contacts'        => $addr->contacts->map(fn($c) => [
                                                    'id'    => $c->id,
                                                    'type'  => $c->type,
                                                    'value' => $c->value,
                                                ])->toArray(),
                                            ];
                                        })->toArray();
                                    }
                                @endphp

                                @forelse($personAddresses as $pIdx => $address)
                                    @include('customers.partials.address_contact', [
                                        'prefix'      => 'person',
                                        'addressIndex'=> $pIdx,
                                        'addressOld'  => $address,
                                        'countries'   => $countries,
                                    ])
                                @empty
                                    @include('customers.partials.address_contact', [
                                        'prefix'      => 'person',
                                        'addressIndex'=> 0,
                                        'addressOld'  => [],
                                        'countries'   => $countries,
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
                    <a href="#company-collapse" class="text-dark collapsed" data-bs-toggle="collapse" aria-controls="company-collapse">
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
                                $company = $customer->company ?? null;
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

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">کد اقتصادی</label>
                                    <input type="text" name="company[economic_code]"
                                           class="form-control @error('company.economic_code') is-invalid @enderror"
                                           value="{{ old('company.economic_code', $company->economic_code ?? '') }}">
                                    @error('company.economic_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
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

                            <h5 class="mt-4">آدرس‌ها و شماره تماس شرکت</h5>
                            <div id="company-addresses">
                                @php
                                    $companyAddresses = old('company.addresses', []);
                                    if (empty($companyAddresses) && $company && $company->addresses) {
                                        $companyAddresses = $company->addresses->map(function($addr) {
                                            $provinceOptions = [];
                                            $cityOptions = [];

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
                                                'id'              => $addr->id,
                                                'country_id'      => $addr->country_id,
                                                'province_id'     => $addr->province_id,
                                                'city_id'         => $addr->city_id,
                                                'postal_code'     => $addr->postal_code,
                                                'address_detail'  => $addr->address_detail,
                                                'province_options'=> $provinceOptions,
                                                'city_options'    => $cityOptions,
                                                'contacts'        => $addr->contacts->map(fn($c) => [
                                                    'id'    => $c->id,
                                                    'type'  => $c->type,
                                                    'value' => $c->value,
                                                ])->toArray(),
                                            ];
                                        })->toArray();
                                    }
                                @endphp

                                @forelse($companyAddresses as $cIdx => $address)
                                    @include('customers.partials.address_contact', [
                                        'prefix'      => 'company',
                                        'addressIndex'=> $cIdx,
                                        'addressOld'  => $address,
                                        'countries'   => $countries,
                                    ])
                                @empty
                                    @include('customers.partials.address_contact', [
                                        'prefix'      => 'company',
                                        'addressIndex'=> 0,
                                        'addressOld'  => [],
                                        'countries'   => $countries,
                                    ])
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-link" onclick="addAddressRow('company')">
                                افزودن آدرس جدید برای شرکت
                            </button>
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

{{-- قالب مخفی برای آدرس جدید --}}
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
    let count = addressesDiv.getElementsByClassName('address-group').length;

    let tpl = document.getElementById('address-template').innerHTML;
    tpl = tpl.replace(/__PREFIX__/g, prefix)
             .replace(/__INDEX__/g, count);

    addressesDiv.insertAdjacentHTML('beforeend', tpl);
}

function addContactRow(prefix, addressIdx) {
    let contactsId = prefix + '-contacts-' + addressIdx;
    let contactsDiv = document.getElementById(contactsId);
    let count = contactsDiv.getElementsByClassName('contact-row').length;

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
</script>

<script>
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('country-select')) {
        let select = e.target;
        let countryId = select.value;
        let prefix = select.dataset.prefix;
        let index  = select.dataset.index;
        let provinceSelect = document.querySelector(
            `select[name="${prefix}[addresses][${index}][province_id]"]`
        );
        let citySelect = document.querySelector(
            `select[name="${prefix}[addresses][${index}][city_id]"]`
        );
        provinceSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
        citySelect.innerHTML = '<option value="">انتخاب کنید</option>';

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
        let select = e.target;
        let provinceId = select.value;
        let prefix = select.dataset.prefix;
        let index  = select.dataset.index;
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
</script>
@endsection

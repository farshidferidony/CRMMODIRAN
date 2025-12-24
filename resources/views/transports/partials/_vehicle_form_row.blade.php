@php
    $isWagon = (bool)($vehicle['is_wagon'] ?? false);
@endphp

<div class="border rounded p-3 mb-3 vehicle-row" data-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>وسیله حمل شماره {{ $index + 1 }}</strong>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-vehicle">
            ×
        </button>
    </div>

    <div class="row">
        {{-- نوع وسیله و شرکت باربری --}}
        <div class="col-md-3 mb-3">
            <label class="form-label">* نوع وسیله</label>
            <select name="vehicles[{{ $index }}][is_wagon]"
                    class="form-control @error('vehicles.'.$index.'.is_wagon') is-invalid @enderror">
                <option value="0" {{ !$isWagon ? 'selected' : '' }}>ماشین جاده‌ای</option>
                <option value="1" {{ $isWagon ? 'selected' : '' }}>واگن</option>
            </select>
            @error('vehicles.'.$index.'.is_wagon')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">* نوع ماشین/واگن</label>
            <input type="text"
                   name="vehicles[{{ $index }}][vehicle_type]"
                   class="form-control @error('vehicles.'.$index.'.vehicle_type') is-invalid @enderror"
                   value="{{ $vehicle['vehicle_type'] ?? '' }}"
                   placeholder="مثلاً تریلی کفی / واگن روسی">
            @error('vehicles.'.$index.'.vehicle_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">* شرکت باربری</label>
            <input type="text"
                   name="vehicles[{{ $index }}][freight_company_name]"
                   class="form-control @error('vehicles.'.$index.'.freight_company_name') is-invalid @enderror"
                   value="{{ $vehicle['freight_company_name'] ?? '' }}"
                   placeholder="نام شرکت باربری">
            @error('vehicles.'.$index.'.freight_company_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- راننده و بارنامه --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">* نام راننده</label>
            <input type="text"
                   name="vehicles[{{ $index }}][driver_name]"
                   class="form-control @error('vehicles.'.$index.'.driver_name') is-invalid @enderror"
                   value="{{ $vehicle['driver_name'] ?? '' }}">
            @error('vehicles.'.$index.'.driver_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">* کد ملی راننده</label>
            <input type="text"
                   name="vehicles[{{ $index }}][driver_national_code]"
                   class="form-control @error('vehicles.'.$index.'.driver_national_code') is-invalid @enderror"
                   value="{{ $vehicle['driver_national_code'] ?? '' }}">
            @error('vehicles.'.$index.'.driver_national_code')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">* موبایل راننده</label>
            <input type="text"
                   name="vehicles[{{ $index }}][driver_mobile]"
                   class="form-control @error('vehicles.'.$index.'.driver_mobile') is-invalid @enderror"
                   value="{{ $vehicle['driver_mobile'] ?? '' }}">
            @error('vehicles.'.$index.'.driver_mobile')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">همراه راننده</label>
            <input type="text"
                   name="vehicles[{{ $index }}][driver_helper]"
                   class="form-control @error('vehicles.'.$index.'.driver_helper') is-invalid @enderror"
                   value="{{ $vehicle['driver_helper'] ?? '' }}">
            @error('vehicles.'.$index.'.driver_helper')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- پلاک ماشین --}}
    <div class="row mb-3">
        <div class="col-12">
            <label class="form-label d-block">شماره پلاک (برای ماشین)</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <input type="number" name="vehicles[{{ $index }}][plate_iran]"
                       class="form-control w-auto @error('vehicles.'.$index.'.plate_iran') is-invalid @enderror"
                       value="{{ $vehicle['plate_iran'] ?? '' }}" placeholder="ایران" min="10" max="99">
                <input type="number" name="vehicles[{{ $index }}][plate_3digit]"
                       class="form-control w-auto @error('vehicles.'.$index.'.plate_3digit') is-invalid @enderror"
                       value="{{ $vehicle['plate_3digit'] ?? '' }}" placeholder="سه رقمی" min="100" max="999">
                <select name="vehicles[{{ $index }}][plate_letter]"
                        class="form-control w-auto @error('vehicles.'.$index.'.plate_letter') is-invalid @enderror">
                    <option value="">حرف</option>
                    @foreach(['ا','ب','پ','ت','ث','ج','چ','ح','خ','د','ذ','ر','ز','ژ','س','ش','ص','ض','ط','ظ','غ','ف','ق','ک','گ','ل','م','ن','و','ه','ی'] as $letter)
                        <option value="{{ $letter }}"
                            {{ ($vehicle['plate_letter'] ?? '') === $letter ? 'selected' : '' }}>
                            {{ $letter }}
                        </option>
                    @endforeach
                </select>
                <input type="number" name="vehicles[{{ $index }}][plate_2digit]"
                       class="form-control w-auto @error('vehicles.'.$index.'.plate_2digit') is-invalid @enderror"
                       value="{{ $vehicle['plate_2digit'] ?? '' }}" placeholder="دو رقمی" min="10" max="99">
            </div>
            @error('vehicles.'.$index.'.plate_iran')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('vehicles.'.$index.'.plate_3digit')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('vehicles.'.$index.'.plate_letter')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('vehicles.'.$index.'.plate_2digit')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- تاریخ‌ها و هزینه‌ها --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">* تاریخ حمل (برنامه‌ریزی‌شده)</label>
            <input type="text"
                   name="vehicles[{{ $index }}][planned_loading_at]"
                   class="form-control vehicle-date @error('vehicles.'.$index.'.planned_loading_at') is-invalid @enderror"
                   value="{{ $vehicle['planned_loading_at'] ?? '' }}"
                   placeholder="تاریخ جلالی">
            @error('vehicles.'.$index.'.planned_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان واقعی بارگیری</label>
            <input type="text"
                   name="vehicles[{{ $index }}][actual_loading_at]"
                   class="form-control vehicle-date @error('vehicles.'.$index.'.actual_loading_at') is-invalid @enderror"
                   value="{{ $vehicle['actual_loading_at'] ?? '' }}"
                   placeholder="تاریخ جلالی">
            @error('vehicles.'.$index.'.actual_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان رسیدن به مقصد</label>
            <input type="text"
                   name="vehicles[{{ $index }}][arrival_at]"
                   class="form-control vehicle-date @error('vehicles.'.$index.'.arrival_at') is-invalid @enderror"
                   value="{{ $vehicle['arrival_at'] ?? '' }}"
                   placeholder="تاریخ جلالی">
            @error('vehicles.'.$index.'.arrival_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان تخلیه</label>
            <input type="text"
                   name="vehicles[{{ $index }}][unloading_at]"
                   class="form-control vehicle-date @error('vehicles.'.$index.'.unloading_at') is-invalid @enderror"
                   value="{{ $vehicle['unloading_at'] ?? '' }}"
                   placeholder="تاریخ جلالی">
            @error('vehicles.'.$index.'.unloading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">کرایه کل (ریال)</label>
            <input type="number"
                   name="vehicles[{{ $index }}][total_freight_amount]"
                   class="form-control @error('vehicles.'.$index.'.total_freight_amount') is-invalid @enderror"
                   value="{{ $vehicle['total_freight_amount'] ?? '' }}">
            @error('vehicles.'.$index.'.total_freight_amount')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">هزینه بارگیری (ریال)</label>
            <input type="number"
                   name="vehicles[{{ $index }}][loading_cost]"
                   class="form-control @error('vehicles.'.$index.'.loading_cost') is-invalid @enderror"
                   value="{{ $vehicle['loading_cost'] ?? '' }}">
            @error('vehicles.'.$index.'.loading_cost')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">برگشتی (ریال)</label>
            <input type="number"
                   name="vehicles[{{ $index }}][return_amount]"
                   class="form-control @error('vehicles.'.$index.'.return_amount') is-invalid @enderror"
                   value="{{ $vehicle['return_amount'] ?? '' }}">
            @error('vehicles.'.$index.'.return_amount')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">هزینه واگن (ریال)</label>
            <input type="number"
                   name="vehicles[{{ $index }}][wagon_cost]"
                   class="form-control @error('vehicles.'.$index.'.wagon_cost') is-invalid @enderror"
                   value="{{ $vehicle['wagon_cost'] ?? '' }}">
            @error('vehicles.'.$index.'.wagon_cost')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- اطلاعات ویژه واگن --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">موبایل هماهنگ‌کننده واگن</label>
            <input type="text"
                   name="vehicles[{{ $index }}][wagon_coordinator_mobile]"
                   class="form-control @error('vehicles.'.$index.'.wagon_coordinator_mobile') is-invalid @enderror"
                   value="{{ $vehicle['wagon_coordinator_mobile'] ?? '' }}">
            @error('vehicles.'.$index.'.wagon_coordinator_mobile')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">شماره تماس واگن</label>
            <input type="text"
                   name="vehicles[{{ $index }}][wagon_contact_phone]"
                   class="form-control @error('vehicles.'.$index.'.wagon_contact_phone') is-invalid @enderror"
                   value="{{ $vehicle['wagon_contact_phone'] ?? '' }}">
            @error('vehicles.'.$index.'.wagon_contact_phone')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">توضیحات</label>
            <textarea name="vehicles[{{ $index }}][description]"
                      class="form-control @error('vehicles.'.$index.'.description') is-invalid @enderror"
                      rows="2">{{ $vehicle['description'] ?? '' }}</textarea>
            @error('vehicles.'.$index.'.description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- فایل‌های پیوست شرکت حمل --}}
    <div class="mb-2">
        <label class="form-label d-block">فایل‌های پیوست شرکت حمل</label>
        <div id="vehicle-files-container-{{ $index }}" class="border rounded p-2">
            <small class="text-muted d-block mb-1">
                برای این وسیله می‌توانید چند فایل با توضیح اضافه کنید.
            </small>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary btn-add-vehicle-file"
                    data-vehicle-index="{{ $index }}">
                + افزودن فایل
            </button>

            @error('vehicles.'.$index.'.files.*.file')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
            @error('vehicles.'.$index.'.files.*.title')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- TODO: دکمه انتخاب آیتم‌ها برای این وسیله (مثل Step 2) --}}
    <div class="mb-2">
        <button type="button"
                class="btn btn-sm btn-outline-primary btn-select-vehicle-items"
                data-vehicle-index="{{ $index }}">
            انتخاب آیتم‌هایی که این وسیله حمل می‌کند
        </button>
        <div class="border rounded p-2 mt-2"
             id="vehicle-items-container-{{ $index }}">
            <small class="text-muted d-block mb-1">
                آیتم‌های انتخابی برای این وسیله در این‌جا نمایش داده می‌شوند.
            </small>
        </div>
    </div>
</div>

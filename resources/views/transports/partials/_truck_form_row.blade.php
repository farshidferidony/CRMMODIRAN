@php
    $idx = (int) $index;
    $rowNumber = is_numeric($index) ? ($idx + 1) : '__INDEX__';
@endphp


<div class="border rounded p-3 mb-3 truck-row" data-index="{{ $idx }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>ماشین شماره {{ $rowNumber }}</strong>
        
            <button type="button"
                    class="btn btn-sm btn-outline-danger btn-remove-truck">
                ×
            </button>
        
        
    </div>

    {{-- hidden is_wagon = 0 --}}
    <input type="hidden" name="trucks[{{ $idx }}][is_wagon]" value="0">

    <div class="row">
        @php
            // مقدار انتخاب‌شده برای این ردیف
            $selectedVehicleType = old("trucks.$idx.vehicle_type", $truck['vehicle_type'] ?? null);
        @endphp

        <div class="col-md-4 mb-3">
            <label class="form-label">* نوع ماشین</label>
            <select name="trucks[{{ $idx }}][vehicle_type]"
                    class="form-control @error('trucks.'.$idx.'.vehicle_type') is-invalid @enderror"
                    required>
                <option value="">انتخاب کنید...</option>
                <option value="lowboy"        {{ $selectedVehicleType === 'lowboy' ? 'selected' : '' }}>کمرشکن</option>
                <option value="flat_trailer"  {{ $selectedVehicleType === 'flat_trailer' ? 'selected' : '' }}>تریلی کفی</option>
                <option value="roll_trailer"  {{ $selectedVehicleType === 'roll_trailer' ? 'selected' : '' }}>تریلی جا رول دار</option>
                <option value="side_trailer"  {{ $selectedVehicleType === 'side_trailer' ? 'selected' : '' }}>تریلی لبه دار</option>
                <option value="ten_wheeler"   {{ $selectedVehicleType === 'ten_wheeler' ? 'selected' : '' }}>ده چرخ</option>
                <option value="single"        {{ $selectedVehicleType === 'single' ? 'selected' : '' }}>تک</option>
                <option value="truck_911"     {{ $selectedVehicleType === 'truck_911' ? 'selected' : '' }}>کامیون ۹۱۱</option>
                <option value="khaawar"       {{ $selectedVehicleType === 'khaawar' ? 'selected' : '' }}>خاور</option>
                <option value="khaawar_steel" {{ $selectedVehicleType === 'khaawar_steel' ? 'selected' : '' }}>خاور آهن‌کش</option>
                <option value="nissan"        {{ $selectedVehicleType === 'nissan' ? 'selected' : '' }}>نیسان</option>
                <option value="nissan_steel"  {{ $selectedVehicleType === 'nissan_steel' ? 'selected' : '' }}>نیسان آهن‌کش</option>
                <option value="pickup"        {{ $selectedVehicleType === 'pickup' ? 'selected' : '' }}>وانت پیکان</option>
                <option value="bunker"        {{ $selectedVehicleType === 'bunker' ? 'selected' : '' }}>بونکر</option>
            </select>
            @error('trucks.'.$idx.'.vehicle_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div class="col-md-4 mb-3">
            <label class="form-label">* شرکت باربری</label>
            <input type="text"
                   name="trucks[{{ $idx }}][freight_company_name]"
                   class="form-control @error('trucks.'.$idx.'.freight_company_name') is-invalid @enderror"
                   value="{{ $truck['freight_company_name'] ?? '' }}"
                   placeholder="نام شرکت باربری">
            @error('trucks.'.$idx.'.freight_company_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @php
            // اگر از آرایه مدل آمده، ممکن است enum باشد؛ آن‌را به string تبدیل می‌کنیم
            $statusRaw = $truck['status'] ?? null;
            $statusValue = $statusRaw instanceof \App\Enums\TransportVehicleStatus
                ? $statusRaw->value
                : $statusRaw;

            $currentStatus = old('trucks.'.$idx.'.status', $statusValue ?? 'searching');
        @endphp

        <div class="col-md-4 mb-3">
            <label class="form-label">وضعیت</label>
            <select name="trucks[{{ $idx }}][status]" class="form-control vehicle-status">
                <option value="searching"  {{ $currentStatus === 'searching'  ? 'selected' : '' }}>در حال جستجو</option>
                <option value="found"      {{ $currentStatus === 'found'      ? 'selected' : '' }}>پیدا شد</option>
                <option value="loading"    {{ $currentStatus === 'loading'    ? 'selected' : '' }}>در حال بارگیری</option>
                <option value="loaded"     {{ $currentStatus === 'loaded'     ? 'selected' : '' }}>بارگیری شده</option>
                <option value="en_route"   {{ $currentStatus === 'en_route'   ? 'selected' : '' }}>در مسیر</option>
                <option value="arrived"    {{ $currentStatus === 'arrived'    ? 'selected' : '' }}>به مقصد رسیده</option>
                <option value="unloading"  {{ $currentStatus === 'unloading'  ? 'selected' : '' }}>در حال تخلیه</option>
                <option value="unloaded"   {{ $currentStatus === 'unloaded'   ? 'selected' : '' }}>تخلیه کامل شد</option>
            </select>
            @error('trucks.'.$idx.'.status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


    </div>

    {{-- راننده --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">* نام راننده</label>
            <input type="text"
                   name="trucks[{{ $idx }}][driver_name]"
                   class="form-control @error('trucks.'.$idx.'.driver_name') is-invalid @enderror"
                   value="{{ $truck['driver_name'] ?? '' }}">
            @error('trucks.'.$idx.'.driver_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">* کد ملی راننده</label>
            <input type="text"
                   name="trucks[{{ $idx }}][driver_national_code]"
                   class="form-control @error('trucks.'.$idx.'.driver_national_code') is-invalid @enderror"
                   value="{{ $truck['driver_national_code'] ?? '' }}">
            @error('trucks.'.$idx.'.driver_national_code')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">* موبایل راننده</label>
            <input type="text"
                   name="trucks[{{ $idx }}][driver_mobile]"
                   class="form-control @error('trucks.'.$idx.'.driver_mobile') is-invalid @enderror"
                   value="{{ $truck['driver_mobile'] ?? '' }}">
            @error('trucks.'.$idx.'.driver_mobile')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">همراه راننده</label>
            <input type="text"
                   name="trucks[{{ $idx }}][driver_helper]"
                   class="form-control @error('trucks.'.$idx.'.driver_helper') is-invalid @enderror"
                   value="{{ $truck['driver_helper'] ?? '' }}">
            @error('trucks.'.$idx.'.driver_helper')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- پلاک ماشین --}}
    <div class="mb-3">
        <label class="form-label d-block">شماره پلاک</label>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="number" name="trucks[{{ $idx }}][plate_iran]"
                   class="form-control w-auto @error('trucks.'.$idx.'.plate_iran') is-invalid @enderror"
                   value="{{ $truck['plate_iran'] ?? '' }}" placeholder="ایران" min="10" max="99">
            <input type="number" name="trucks[{{ $idx }}][plate_3digit]"
                   class="form-control w-auto @error('trucks.'.$idx.'.plate_3digit') is-invalid @enderror"
                   value="{{ $truck['plate_3digit'] ?? '' }}" placeholder="سه رقمی" min="100" max="999">
            <select name="trucks[{{ $idx }}][plate_letter]"
                    class="form-control w-auto @error('trucks.'.$idx.'.plate_letter') is-invalid @enderror">
                <option value="">حرف</option>
                @foreach(['ا','ب','پ','ت','ث','ج','چ','ح','خ','د','ذ','ر','ز','ژ','س','ش','ص','ض','ط','ظ','غ','ف','ق','ک','گ','ل','م','ن','و','ه','ی'] as $letter)
                    <option value="{{ $letter }}"
                        {{ ($truck['plate_letter'] ?? '') === $letter ? 'selected' : '' }}>
                        {{ $letter }}
                    </option>
                @endforeach
            </select>
            <input type="number" name="trucks[{{ $idx }}][plate_2digit]"
                   class="form-control w-auto @error('trucks.'.$idx.'.plate_2digit') is-invalid @enderror"
                   value="{{ $truck['plate_2digit'] ?? '' }}" placeholder="دو رقمی" min="10" max="99">
        </div>
        @error('trucks.'.$idx.'.plate_iran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        @error('trucks.'.$idx.'.plate_3digit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        @error('trucks.'.$idx.'.plate_letter') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        @error('trucks.'.$idx.'.plate_2digit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- تاریخ و هزینه‌ها --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">* تاریخ حمل (جلالی)</label>
            <input type="text"
                   name="trucks[{{ $idx }}][planned_loading_at]"
                   class="form-control vehicle-date @error('trucks.'.$idx.'.planned_loading_at') is-invalid @enderror"
                   value="{{ $truck['planned_loading_at'] ?? '' }}">
            @error('trucks.'.$idx.'.planned_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان واقعی بارگیری</label>
            <input type="text"
                   name="trucks[{{ $idx }}][actual_loading_at]"
                   class="form-control vehicle-date @error('trucks.'.$idx.'.actual_loading_at') is-invalid @enderror"
                   value="{{ old('trucks.'.$idx.'.actual_loading_at', $truck['actual_loading_at'] ?? '') }}">
            @error('trucks.'.$idx.'.actual_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان رسیدن</label>
            <input type="text"
                   name="trucks[{{ $idx }}][arrival_at]"
                   class="form-control vehicle-date @error('trucks.'.$idx.'.arrival_at') is-invalid @enderror"
                   value="{{ old('trucks.'.$idx.'.arrival_at', $truck['arrival_at'] ?? '') }}">
            @error('trucks.'.$idx.'.arrival_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان تخلیه</label>
            <input type="text"
                   name="trucks[{{ $idx }}][unloading_at]"
                   class="form-control vehicle-date @error('trucks.'.$idx.'.unloading_at') is-invalid @enderror"
                   value="{{ old('trucks.'.$idx.'.unloading_at', $truck['unloading_at'] ?? '') }}">
            @error('trucks.'.$idx.'.unloading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">کرایه کل (ریال)</label>
            <input type="number"
                   name="trucks[{{ $idx }}][total_freight_amount]"
                   class="form-control @error('trucks.'.$idx.'.total_freight_amount') is-invalid @enderror"
                   value="{{ $truck['total_freight_amount'] ?? '' }}">
            @error('trucks.'.$idx.'.total_freight_amount')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">هزینه بارگیری (ریال)</label>
            <input type="number"
                   name="trucks[{{ $idx }}][loading_cost]"
                   class="form-control @error('trucks.'.$idx.'.loading_cost') is-invalid @enderror"
                   value="{{ $truck['loading_cost'] ?? '' }}">
            @error('trucks.'.$idx.'.loading_cost')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">برگشتی (ریال)</label>
            <input type="number"
                   name="trucks[{{ $idx }}][return_amount]"
                   class="form-control @error('trucks.'.$idx.'.return_amount') is-invalid @enderror"
                   value="{{ $truck['return_amount'] ?? '' }}">
            @error('trucks.'.$idx.'.return_amount')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- شماره بارنامه + توضیحات --}}
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">شماره بارنامه</label>
            <input type="text"
                   name="trucks[{{ $idx }}][bill_of_lading_number]"
                   class="form-control @error('trucks.'.$idx.'.bill_of_lading_number') is-invalid @enderror"
                   value="{{ $truck['bill_of_lading_number'] ?? '' }}">
            @error('trucks.'.$idx.'.bill_of_lading_number')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">توضیحات</label>
            <textarea name="trucks[{{ $idx }}][description]"
                      class="form-control @error('trucks.'.$idx.'.description') is-invalid @enderror"
                      rows="2">{{ $truck['description'] ?? '' }}</textarea>
            @error('trucks.'.$idx.'.description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @php
        $existingItems = $truck['items'] ?? [];
    @endphp

    <div class="mb-2">
        <button type="button"
                class="btn btn-sm btn-outline-primary btn-select-vehicle-items"
                data-vehicle-type="truck"
                data-vehicle-index="{{ $idx }}">
            انتخاب آیتم‌هایی که این ماشین حمل می‌کند
        </button>

        <div class="border rounded p-2 mt-2"
            id="vehicle-items-container-truck-{{ $idx }}">
            @if(empty($existingItems))
                <small class="text-muted d-block mb-1">
                    آیتمی انتخاب نشده است.
                </small>
            @else
                @foreach($existingItems as $iIndex => $item)
                    @php
                        $loadingId = $item['transport_loading_id'] ?? $item['loading_id'] ?? null;
                        $productId = $item['product_id'] ?? null;
                        $quantity  = $item['quantity'] ?? null;
                        $product   = $item['product']['name'] ?? $item['product']['title'] ?? $productId;
                    @endphp
                    <div class="small mb-1">
                        <span class="badge bg-light text-dark">
                            لودینگ {{ $loadingId }} | محصول {{ $product }} | مقدار: {{ $quantity }}
                        </span>
                        <input type="hidden"
                            name="trucks[{{ $idx }}][items][{{ $iIndex }}][loading_id]"
                            value="{{ $loadingId }}">
                        <input type="hidden"
                            name="trucks[{{ $idx }}][items][{{ $iIndex }}][product_id]"
                            value="{{ $productId }}">
                        <input type="hidden"
                            name="trucks[{{ $idx }}][items][{{ $iIndex }}][quantity]"
                            value="{{ $quantity }}">
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @php
        /** @var \App\Models\TransportVehicle|null $vehicleModel */

        // اگر truck آرایه‌ی کنترلر است و model دارد:
        if (is_array($truck) && array_key_exists('model', $truck)) {
            $vehicleModel = $truck['model'];
        } else {
            $vehicleModel = null;
        }

        $existingFiles = $vehicleModel?->files ?? collect();

        // id هم ممکن است از آرایه یا از مدل بیاید (برای حالت old)
        $vehicleId = $truck['id'] ?? $vehicleModel?->id ?? null;
    @endphp

    <div class="mb-3">
        <label class="form-label d-block">فایل‌های مربوط به این ماشین</label>

        {{-- فایل‌های موجود --}}
        @foreach($existingFiles as $file)
            @php
                $url = $file->path ? Storage::disk('public')->url($file->path) : null;
            @endphp
            <div class="row g-2 mb-2 align-items-center">
                <div class="col-md-7">
                    <div class="d-flex flex-column">
                        <span>{{ $file->title ?? 'بدون عنوان' }}</span>
                        @if($url)
                            <a href="{{ $url }}" target="_blank">مشاهده / دانلود</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-check">
                        <input class="form-check-input"
                            type="checkbox"
                            name="trucks_existing_files_to_delete[]"
                            value="{{ $file->id }}"
                            id="truck-file-del-{{ $file->id }}">
                        <label class="form-check-label" for="truck-file-del-{{ $file->id }}">
                            حذف این فایل
                        </label>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- فایل‌های جدید (آپلود) --}}
        <div class="truck-files-container" data-index="{{ $idx }}">
            <div class="row g-2 mb-2 truck-file-row">
                <div class="col-md-4">
                    <input type="text"
                        name="trucks[{{ $idx }}][files][0][title]"
                        class="form-control"
                        placeholder="عنوان فایل (مثلاً بارنامه)">
                </div>
                <div class="col-md-6">
                    <input type="file"
                        name="trucks[{{ $idx }}][files][0][file]"
                        class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger btn-remove-truck-file">
                        ×
                    </button>
                </div>
            </div>
        </div>

        <button type="button"
                class="btn btn-sm btn-outline-primary btn-add-truck-file"
                data-index="{{ $idx }}">
            + افزودن فایل جدید
        </button>
    </div>

    @php
        $fasRaw = $truck['freight_accounting_status'] ?? null;
        $fasValue = $fasRaw instanceof \App\Enums\FreightAccountingStatus
            ? $fasRaw->value
            : ($fasRaw ?? 'pending');
        $freightRejectReason = $truck['freight_reject_reason'] ?? null;
    @endphp

    <div class="mb-2 vehicle-accounting-status">
        <strong>وضعیت حسابداری کرایه:</strong>

        @if($fasValue === 'pending')
            <span class="badge bg-warning text-dark">در انتظار تأیید حسابداری</span>
        @elseif($fasValue === 'approved')
            <span class="badge bg-success">تأیید شده</span>
        @elseif($fasValue === 'rejected')
            <span class="badge bg-danger">رد شده</span>
            @if($freightRejectReason)
                <small class="text-muted d-block">علت: {{ $freightRejectReason }}</small>
            @endif
        @endif
    </div>

    @if($vehicleId)
        <input type="hidden"
            name="trucks[{{ $idx }}][id]"
            value="{{ $vehicleId }}">
    @endif

    {{-- فایل‌ها و آیتم‌ها را بعداً اضافه می‌کنیم --}}
</div>

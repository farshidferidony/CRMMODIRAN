@php
    $idx = (int) $index;
    $rowNumber = is_numeric($index) ? ($idx + 1) : '__INDEX__';
@endphp


<div class="border rounded p-3 mb-3 wagon-row" data-index="{{ $idx }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>واگن شماره {{ $rowNumber }}</strong>
        
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-wagon">
                ×
            </button>
        

    </div>

    <input type="hidden" name="wagons[{{ $idx }}][is_wagon]" value="1">

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">* نوع واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][vehicle_type]"
                   class="form-control @error('wagons.'.$idx.'.vehicle_type') is-invalid @enderror"
                   value="{{ $wagon['vehicle_type'] ?? '' }}"
                   placeholder="مثلاً واگن روسی">
            @error('wagons.'.$idx.'.vehicle_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">* شرکت باربری / راه‌آهن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][freight_company_name]"
                   class="form-control @error('wagons.'.$idx.'.freight_company_name') is-invalid @enderror"
                   value="{{ $wagon['freight_company_name'] ?? '' }}"
                   placeholder="نام شرکت / راه‌آهن">
            @error('wagons.'.$idx.'.freight_company_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @php
            $statusRaw = $wagon['status'] ?? null;
            $statusValue = $statusRaw instanceof \App\Enums\TransportVehicleStatus
                ? $statusRaw->value
                : $statusRaw;

            $currentStatus = old('wagons.'.$idx.'.status', $statusValue ?? 'searching');
        @endphp

        <div class="col-md-4 mb-3">
            <label class="form-label">وضعیت واگن</label>
            <select name="wagons[{{ $idx }}][status]" class="form-control vehicle-status">
                <option value="searching"  {{ $currentStatus === 'searching'  ? 'selected' : '' }}>در حال جستجو</option>
                <option value="found"      {{ $currentStatus === 'found'      ? 'selected' : '' }}>پیدا شد</option>
                <option value="loading"    {{ $currentStatus === 'loading'    ? 'selected' : '' }}>در حال بارگیری</option>
                <option value="loaded"     {{ $currentStatus === 'loaded'     ? 'selected' : '' }}>بارگیری شده</option>
                <option value="en_route"   {{ $currentStatus === 'en_route'   ? 'selected' : '' }}>در مسیر</option>
                <option value="arrived"    {{ $currentStatus === 'arrived'    ? 'selected' : '' }}>به مقصد رسیده</option>
                <option value="unloading"  {{ $currentStatus === 'unloading'  ? 'selected' : '' }}>در حال تخلیه</option>
                <option value="unloaded"   {{ $currentStatus === 'unloaded'   ? 'selected' : '' }}>تخلیه کامل شد</option>
            </select>
            @error('wagons.'.$idx.'.status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


    </div>

    {{-- تاریخ‌ها و هزینه واگن --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">* تاریخ بارگیری واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][planned_loading_at]"
                   class="form-control vehicle-date @error('wagons.'.$idx.'.planned_loading_at') is-invalid @enderror"
                   value="{{ $wagon['planned_loading_at'] ?? '' }}">
            @error('wagons.'.$idx.'.planned_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان حرکت واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][actual_loading_at]"
                   class="form-control vehicle-date @error('wagons.'.$idx.'.actual_loading_at') is-invalid @enderror"
                   value="{{ $wagon['actual_loading_at'] ?? '' }}">
            @error('wagons.'.$idx.'.actual_loading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان رسیدن واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][arrival_at]"
                   class="form-control vehicle-date @error('wagons.'.$idx.'.arrival_at') is-invalid @enderror"
                   value="{{ $wagon['arrival_at'] ?? '' }}">
            @error('wagons.'.$idx.'.arrival_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">زمان تخلیه واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][unloading_at]"
                   class="form-control vehicle-date @error('wagons.'.$idx.'.unloading_at') is-invalid @enderror"
                   value="{{ $wagon['unloading_at'] ?? '' }}">
            @error('wagons.'.$idx.'.unloading_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">هزینه واگن (ریال)</label>
            <input type="number"
                   name="wagons[{{ $idx }}][wagon_cost]"
                   class="form-control @error('wagons.'.$idx.'.wagon_cost') is-invalid @enderror"
                   value="{{ $wagon['wagon_cost'] ?? '' }}">
            @error('wagons.'.$idx.'.wagon_cost')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">موبایل هماهنگ‌کننده واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][wagon_coordinator_mobile]"
                   class="form-control @error('wagons.'.$idx.'.wagon_coordinator_mobile') is-invalid @enderror"
                   value="{{ $wagon['wagon_coordinator_mobile'] ?? '' }}">
            @error('wagons.'.$idx.'.wagon_coordinator_mobile')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">شماره تماس واگن</label>
            <input type="text"
                   name="wagons[{{ $idx }}][wagon_contact_phone]"
                   class="form-control @error('wagons.'.$idx.'.wagon_contact_phone') is-invalid @enderror"
                   value="{{ $wagon['wagon_contact_phone'] ?? '' }}">
            @error('wagons.'.$idx.'.wagon_contact_phone')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        @php
            $existingItems = $wagon['items'] ?? [];
        @endphp

        <div class="mb-2">
            <button type="button"
                    class="btn btn-sm btn-outline-primary btn-select-vehicle-items"
                    data-vehicle-type="wagon"
                    data-vehicle-index="{{ $idx }}">
                انتخاب آیتم‌هایی که این واگن حمل می‌کند
            </button>

            <div class="border rounded p-2 mt-2"
                id="vehicle-items-container-wagon-{{ $idx }}">
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
                                name="wagons[{{ $idx }}][items][{{ $iIndex }}][loading_id]"
                                value="{{ $loadingId }}">
                            <input type="hidden"
                                name="wagons[{{ $idx }}][items][{{ $iIndex }}][product_id]"
                                value="{{ $productId }}">
                            <input type="hidden"
                                name="wagons[{{ $idx }}][items][{{ $iIndex }}][quantity]"
                                value="{{ $quantity }}">
                        </div>
                    @endforeach
                @endif
            </div>
        </div>



        <div class="col-md-3 mb-3">
            <label class="form-label">شماره بارنامه / واگن‌نامه</label>
            <input type="text"
                   name="wagons[{{ $idx }}][bill_of_lading_number]"
                   class="form-control @error('wagons.'.$idx.'.bill_of_lading_number') is-invalid @enderror"
                   value="{{ $wagon['bill_of_lading_number'] ?? '' }}">
            @error('wagons.'.$idx.'.bill_of_lading_number')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">توضیحات</label>
        <textarea name="wagons[{{ $idx }}][description]"
                  class="form-control @error('wagons.'.$idx.'.description') is-invalid @enderror"
                  rows="2">{{ $wagon['description'] ?? '' }}</textarea>
        @error('wagons.'.$idx.'.description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label">توضیحات</label>
        <textarea name="wagons[{{ $idx }}][description]"
                  class="form-control @error('wagons.'.$idx.'.description') is-invalid @enderror"
                  rows="2">{{ $wagon['description'] ?? '' }}</textarea>
        @error('wagons.'.$idx.'.description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


      @php
        /** @var \App\Models\TransportVehicle|null $wagonModel */

        if (is_array($wagon) && array_key_exists('model', $wagon)) {
            $wagonModel = $wagon['model'];
        } else {
            $wagonModel = null;
        }

        $existingFiles = $wagonModel?->files ?? collect();

        $wagonId = $wagon['id'] ?? $wagonModel?->id ?? null;

        $fasRaw = $wagon['freight_accounting_status'] ?? null;
        $fasValue = $fasRaw instanceof \App\Enums\FreightAccountingStatus
            ? $fasRaw->value
            : ($fasRaw ?? 'pending');
        $freightRejectReason = $wagon['freight_reject_reason'] ?? null;
    @endphp

    <div class="mb-3">
        <label class="form-label d-block">فایل‌های مربوط به این واگن</label>

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
                               name="wagons_existing_files_to_delete[]"
                               value="{{ $file->id }}"
                               id="wagon-file-del-{{ $file->id }}">
                        <label class="form-check-label" for="wagon-file-del-{{ $file->id }}">
                            حذف این فایل
                        </label>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- فایل‌های جدید (آپلود) --}}
        <div class="wagon-files-container" data-index="{{ $idx }}">
            <div class="row g-2 mb-2 wagon-file-row">
                <div class="col-md-4">
                    <input type="text"
                           name="wagons[{{ $idx }}][files][0][title]"
                           class="form-control"
                           placeholder="عنوان فایل (مثلاً بارنامه)">
                </div>
                <div class="col-md-6">
                    <input type="file"
                           name="wagons[{{ $idx }}][files][0][file]"
                           class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger btn-remove-wagon-file">
                        ×
                    </button>
                </div>
            </div>
        </div>

        <button type="button"
                class="btn btn-sm btn-outline-primary btn-add-wagon-file"
                data-index="{{ $idx }}">
            + افزودن فایل جدید
        </button>
    </div>

    <div class="mb-2 wagon-accounting-status">
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

    @if($wagonId)
        <input type="hidden"
               name="wagons[{{ $idx }}][id]"
               value="{{ $wagonId }}">
    @endif


</div>

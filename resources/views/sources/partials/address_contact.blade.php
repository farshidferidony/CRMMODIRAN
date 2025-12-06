@php
    $addressIndex = $addressIndex ?? 0;
    $address = $addressOld ?? null;

    $addr = $addressOld ?? [];
@endphp

<div class="address-group mb-4 border rounded p-3">
    <div class="row mb-2">
        <div class="col-md-4 mb-1">
            <label class="form-label">کشور</label>
            <select name="{{ $prefix }}[addresses][{{ $addressIndex }}][country_id]"
                    class="form-control country-select"
                    data-prefix="{{ $prefix }}"
                    data-index="{{ $addressIndex }}">
                <option value="">انتخاب کنید</option>
                @foreach($countries as $c)
                    <option value="{{ $c->id }}"
                        @if(isset($addr['country_id']) && $addr['country_id'] == $c->id) selected @endif>
                        {{ app()->getLocale() == 'fa' ? $c->name_fa : $c->name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">استان</label>
            <select name="{{ $prefix }}[addresses][{{ $addressIndex }}][province_id]"
                    class="form-control province-select"
                    data-prefix="{{ $prefix }}"
                    data-index="{{ $addressIndex }}">
                <option value="">انتخاب کنید</option>
                @if(isset($addr['province_options']))
                    @foreach($addr['province_options'] as $p)
                        <option value="{{ $p->id }}"
                            @if(isset($addr['province_id']) && $addr['province_id'] == $p->id) selected @endif>
                            {{ app()->getLocale() == 'fa' ? $p->name_fa : $p->name_en }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">شهر</label>
            <select name="{{ $prefix }}[addresses][{{ $addressIndex }}][city_id]"
                    class="form-control city-select"
                    data-prefix="{{ $prefix }}"
                    data-index="{{ $addressIndex }}">
                <option value="">انتخاب کنید</option>
                @if(isset($addr['city_options']))
                    @foreach($addr['city_options'] as $c)
                        <option value="{{ $c->id }}"
                            @if(isset($addr['city_id']) && $addr['city_id'] == $c->id) selected @endif>
                            {{ app()->getLocale() == 'fa' ? $c->name_fa : $c->name_en }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="col-md-3 mb-1">
            <input type="text"
                   name="{{ $prefix }}[addresses][{{ $addressIndex }}][postal_code]"
                   class="form-control"
                   placeholder="کدپستی"
                   value="{{ $address['postal_code'] ?? '' }}">
        </div>

    </div>

    <div class="mb-2">
        <input type="text"
               name="{{ $prefix }}[addresses][{{ $addressIndex }}][address_detail]"
               class="form-control"
               placeholder="آدرس"
               value="{{ $address['address_detail'] ?? '' }}">
    </div>
    <div id="{{ $prefix }}-contacts-{{ $addressIndex }}">
        @php
            $contacts = $address['contacts'] ?? [ ['type' => 'mobile', 'value' => ''] ];
        @endphp
        @foreach($contacts as $cIdx => $contact)
            <div class="contact-row row mb-2">
                <div class="col-md-4">
                    <select name="{{ $prefix }}[addresses][{{ $addressIndex }}][contacts][{{ $cIdx }}][type]" class="form-control">
                        <option value="mobile" @if($contact['type']=='mobile') selected @endif>موبایل</option>
                        <option value="phone" @if($contact['type']=='phone') selected @endif>تلفن ثابت</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text"
                           name="{{ $prefix }}[addresses][{{ $addressIndex }}][contacts][{{ $cIdx }}][value]"
                           class="form-control"
                           placeholder="شماره"
                           value="{{ $contact['value'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="addContactRow('{{ $prefix }}', {{ $addressIndex }})">+</button>
                    @if($cIdx > 0)
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">-</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

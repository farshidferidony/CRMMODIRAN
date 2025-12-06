<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CountryFaEn;
use App\Models\ProvinceFaEn;
use App\Models\CityFaEn;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Source::with('company', 'addresses.contacts', 'addresses');

        // نوع منبع (اختیاری اگر type داری: individual/company/both)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // جستجو بر اساس نام/ایمیل
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')
                  ->orWhere('last_name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // پاسپورت/کد ملی
        if ($request->filled('code')) {
            $code = $request->input('code');
            $query->where(function ($q) use ($code) {
                $q->where('national_code', 'like', '%'.$code.'%')
                  ->orWhere('passport_number', 'like', '%'.$code.'%');
            });
        }

        // شرکت
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // کشور/استان/شهر
        if ($request->filled('country_id')) {
            $countryId = $request->input('country_id');
            $query->whereHas('addresses', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }
        if ($request->filled('province_id')) {
            $provinceId = $request->input('province_id');
            $query->whereHas('addresses', function ($q) use ($provinceId) {
                $q->where('province_id', $provinceId);
            });
        }
        if ($request->filled('city_id')) {
            $cityId = $request->input('city_id');
            $query->whereHas('addresses', function ($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
        }

        // شماره تماس
        if ($request->filled('phone')) {
            $phone = $request->input('phone');
            $query->whereHas('addresses.contacts', function ($q) use ($phone) {
                $q->where('value', 'like', '%'.$phone.'%');
            });
        }

        // مرتب‌سازی
        $sortBy    = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $sources   = $query->paginate(20);
        $companies = Company::all();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        $provinces = collect();
        $cities    = collect();

        return view('sources.index', compact('sources','companies','countries','provinces','cities'));
    }

    public function create()
    {
        $companies = Company::all();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        return view('sources.create', compact('companies','countries'));
    }

    public function store(Request $request)
    {
        $hasPerson  = !empty($request->input('person.first_name'));
        $hasCompany = !empty($request->input('company.name'));
        
        

        // منبع حقیقی (شخص)
        if ($hasPerson) {
            $validated = $request->validate([
                'person.first_name'      => 'required|string|max:190',
                'person.last_name'       => 'required|string|max:190',
                'person.passport_number' => 'nullable|string|max:50|unique:sources,passport_number',
                'person.national_code'   => 'nullable|digits:10|unique:sources,national_code',
                'person.birthdate'       => 'nullable|date',
                'person.email'           => 'nullable|email|max:190|unique:sources,email',
            ]);

            $personAddresses = $request->input('person.addresses', []);
            // این‌جا اگر بخواهی می‌توانی مثل مشتری اعتبارسنجی موبایل بزنی

            $source = Source::create([
                'type'           => 'individual',
                'first_name'     => $request->person['first_name'],
                'last_name'      => $request->person['last_name'],
                'passport_number'=> $request->person['passport_number'],
                'national_code'  => $request->person['national_code'],
                'birthdate'      => $request->person['birthdate'],
                'email'          => $request->person['email'],
            ]);

            foreach ($personAddresses as $address) {
                $addr = $source->addresses()->create([
                    'country_id'     => $address['country_id']  ?? null,
                    'province_id'    => $address['province_id'] ?? null,
                    'city_id'        => $address['city_id']     ?? null,
                    'postal_code'    => $address['postal_code'] ?? '',
                    'address_detail' => $address['address_detail'] ?? '',
                ]);
                if (!empty($address['contacts'])) {
                    foreach ($address['contacts'] as $contact) {
                        if (!empty($contact['value'])) {
                            $addr->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
        }

        // شرکت منبع (حقوقی)
        $company = null;
        if ($hasCompany) {
            $validatedCompany = $request->validate([
                'company.name'               => 'required|string|max:190|unique:companies,name',
                'company.economic_code'      => 'required|string|max:50|unique:companies,economic_code',
                'company.registration_number'=> 'required|string|max:50|unique:companies,registration_number',
                'company.email'              => 'nullable|email|max:190|unique:companies,email',
            ]);

            // print_r($validatedCompany);die();

            $companyAddresses = $request->input('company.addresses', []);

            $company = Company::create([
                'name'               => $request->company['name'],
                'economic_code'      => $request->company['economic_code'],
                'registration_number'=> $request->company['registration_number'],
                'email'              => $request->company['email'],
            ]);

            foreach ($companyAddresses as $address) {
                $addr = $company->addresses()->create([
                    'country_id'     => $address['country_id']  ?? null,
                    'province_id'    => $address['province_id'] ?? null,
                    'city_id'        => $address['city_id']     ?? null,
                    'postal_code'    => $address['postal_code'] ?? '',
                    'address_detail' => $address['address_detail'] ?? '',
                ]);
                if (!empty($address['contacts'])) {
                    foreach ($address['contacts'] as $contact) {
                        if (!empty($contact['value'])) {
                            $addr->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
        }

        // اگر منبع هم شخص دارد هم شرکت، شرکت را به منبع وصل کن
        if (isset($source) && $company) {
            $source->company_id = $company->id;
            $source->type       = 'both';
            $source->save();
        }

        return redirect()->route('sources.index')->with('success', 'منبع با موفقیت ثبت شد!');
    }

    public function show(Source $source)
    {
        $source->load(
            'company.addresses.country','company.addresses.province','company.addresses.city',
            'addresses.country','addresses.province','addresses.city','addresses.contacts'
        );
        return view('sources.show', compact('source'));
    }

    public function edit(Source $source)
    {
        $source->load(
            'company.addresses.contacts','addresses.contacts',
            'addresses.country','addresses.province','addresses.city',
            'company.addresses.country','company.addresses.province','company.addresses.city'
        );

        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        return view('sources.edit', compact('source','countries'));
    }

    public function update(Request $request, Source $source)
    {
        $hasPerson  = !empty($request->input('person.first_name'));
        $hasCompany = !empty($request->input('company.name'));

        // شخص
        if ($hasPerson) {
            $validated = $request->validate([
                'person.first_name'      => 'required|string|max:190',
                'person.last_name'       => 'required|string|max:190',
                'person.passport_number' => 'nullable|string|max:50|unique:sources,passport_number,'.$source->id,
                'person.national_code'   => 'nullable|digits:10|unique:sources,national_code,'.$source->id,
                'person.birthdate'       => 'nullable|date',
                'person.email'           => 'nullable|email|max:190|unique:sources,email,'.$source->id,
            ]);

            $source->update([
                'first_name'     => $request->person['first_name'],
                'last_name'      => $request->person['last_name'],
                'passport_number'=> $request->person['passport_number'],
                'national_code'  => $request->person['national_code'],
                'birthdate'      => $request->person['birthdate'],
                'email'          => $request->person['email'],
            ]);

            $incomingAddresses = $request->input('person.addresses', []);
            $existingAddresses = $source->addresses()->get();

            $incomingIds = [];
            foreach ($incomingAddresses as $idx => $adr) {
                $addrId = $adr['id'] ?? null;

                if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
                    $existing->update([
                        'country_id'     => $adr['country_id'] ?? null,
                        'province_id'    => $adr['province_id'] ?? null,
                        'city_id'        => $adr['city_id'] ?? null,
                        'postal_code'    => $adr['postal_code'] ?? '',
                        'address_detail' => $adr['address_detail'] ?? '',
                    ]);
                    $incomingIds[] = $addrId;

                    $contactsData     = $adr['contacts'] ?? [];
                    $existingContacts = $existing->contacts()->get();
                    $contactIds       = [];

                    foreach ($contactsData as $cK => $contact) {
                        $cId = $contact['id'] ?? null;
                        if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
                            $exContact->update([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                            $contactIds[] = $cId;
                        } elseif (!empty($contact['value'])) {
                            $newContact = $existing->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                            $contactIds[] = $newContact->id;
                        }
                    }
                    $existing->contacts()->whereNotIn('id', $contactIds)->delete();

                } else {
                    $newAddress = $source->addresses()->create([
                        'country_id'     => $adr['country_id'] ?? null,
                        'province_id'    => $adr['province_id'] ?? null,
                        'city_id'        => $adr['city_id'] ?? null,
                        'postal_code'    => $adr['postal_code'] ?? '',
                        'address_detail' => $adr['address_detail'] ?? '',
                    ]);
                    $incomingIds[] = $newAddress->id;

                    foreach (($adr['contacts'] ?? []) as $contact) {
                        if (!empty($contact['value'])) {
                            $newAddress->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
            $source->addresses()->whereNotIn('id', $incomingIds)->delete();
        }

        // شرکت منبع
        $company = null;
        if ($hasCompany) {
            $company = $source->company ?? new Company();

            $validatedCompany = $request->validate([
                'company.name'               => 'required|string|max:190|unique:companies,name,' . ($company->id ?? 'NULL') . ',id',
                'company.economic_code'      => 'required|string|max:50|unique:companies,economic_code,' . ($company->id ?? 'NULL') . ',id',
                'company.registration_number'=> 'required|string|max:50|unique:companies,registration_number,' . ($company->id ?? 'NULL') . ',id',
                'company.email'              => 'nullable|email|max:190|unique:companies,email,' . ($company->id ?? 'NULL') . ',id',
            ]);

            $company->fill([
                'name'               => $request->company['name'],
                'economic_code'      => $request->company['economic_code'],
                'registration_number'=> $request->company['registration_number'],
                'email'              => $request->company['email'],
            ]);
            $company->save();

            $incomingAddresses = $request->input('company.addresses', []);
            $existingAddresses = $company->addresses()->get();
            $incomingIds       = [];

            foreach ($incomingAddresses as $idx => $adr) {
                $addrId = $adr['id'] ?? null;

                if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
                    $existing->update([
                        'country_id'     => $adr['country_id'] ?? null,
                        'province_id'    => $adr['province_id'] ?? null,
                        'city_id'        => $adr['city_id'] ?? null,
                        'postal_code'    => $adr['postal_code'] ?? '',
                        'address_detail' => $adr['address_detail'] ?? '',
                    ]);
                    $incomingIds[] = $addrId;

                    $contactsData     = $adr['contacts'] ?? [];
                    $existingContacts = $existing->contacts()->get();
                    $contactIds       = [];

                    foreach ($contactsData as $cK => $contact) {
                        $cId = $contact['id'] ?? null;
                        if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
                            $exContact->update([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                            $contactIds[] = $cId;
                        } elseif (!empty($contact['value'])) {
                            $newContact = $existing->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                            $contactIds[] = $newContact->id;
                        }
                    }
                    $existing->contacts()->whereNotIn('id', $contactIds)->delete();

                } else {
                    $newAddress = $company->addresses()->create([
                        'country_id'     => $adr['country_id'] ?? null,
                        'province_id'    => $adr['province_id'] ?? null,
                        'city_id'        => $adr['city_id'] ?? null,
                        'postal_code'    => $adr['postal_code'] ?? '',
                        'address_detail' => $adr['address_detail'] ?? '',
                    ]);
                    $incomingIds[] = $newAddress->id;

                    foreach (($adr['contacts'] ?? []) as $contact) {
                        if (!empty($contact['value'])) {
                            $newAddress->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
            $company->addresses()->whereNotIn('id', $incomingIds)->delete();

            if (!$source->company_id || $source->company_id !== $company->id) {
                $source->company_id = $company->id;
                $source->save();
            }
        }

        return redirect()->route('sources.index')->with('success', 'اطلاعات منبع با موفقیت ویرایش شد!');
    }

    public function destroy(Source $source)
    {
        $source->delete();
        return redirect()->route('sources.index')->with('success', 'منبع حذف شد!');
    }
}

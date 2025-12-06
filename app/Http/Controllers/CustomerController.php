<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Address;
use App\Models\CountryFaEn;
use App\Models\ProvinceFaEn;
use App\Models\CityFaEn;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // $query = Customer::with('company', 'contacts', 'addresses');
        $query = Customer::with('companies', 'addresses.contacts', 'addresses');

        // فیلتر بر اساس نوع مشتری
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // جستجو بر اساس نام
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // جستجو بر اساس کد ملی یا پاسپورت
        if ($request->filled('code')) {
            $code = $request->input('code');
            $query->where(function ($q) use ($code) {
                $q->where('national_code', 'like', '%' . $code . '%')
                  ->orWhere('passport_number', 'like', '%' . $code . '%');
            });
        }

        // جستجو بر اساس شرکت
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        
        // فیلتر بر اساس کشور
        if ($request->filled('country_id')) {
            $countryId = $request->input('country_id');
            $query->whereHas('addresses', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }

        // فیلتر بر اساس استان
        if ($request->filled('province_id')) {
            $provinceId = $request->input('province_id');
            $query->whereHas('addresses', function ($q) use ($provinceId) {
                $q->where('province_id', $provinceId);
            });
        }

        // فیلتر بر اساس شهر
        if ($request->filled('city_id')) {
            $cityId = $request->input('city_id');
            $query->whereHas('addresses', function ($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
        }

        // جستجو بر اساس شماره تماس
        if ($request->filled('phone')) {
            $phone = $request->input('phone');
            $query->whereHas('contacts', function ($q) use ($phone) {
                $q->where('value', 'like', '%' . $phone . '%');
            });
        }

        // مرتب‌سازی
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // $query->appends($request->except('page'));

        // pagination
        // print_r($query);die();
        $customers = $query->paginate(20);
        $companies = Company::all();
        
        // برای فیلتر استان و شهر
        // $countries = Address::distinct()->pluck('country');
        // $provinces = Address::distinct()->pluck('province');
        // $cities = Address::distinct()->pluck('city');
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        $provinces = collect(); // خالی، بعداً با JS پر می‌کنی
        $cities    = collect();




        return view('customers.index', compact('customers', 'companies', 'countries', 'provinces', 'cities'));
    }

    public function create()
    {
        $companies = Company::all();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        return view('customers.create', compact('companies', 'countries'));
    }

    public function store(Request $request)
    {
        // print_r($request);die();
        $hasPerson = !empty($request->input('person.first_name'));
        $hasCompany = !empty($request->input('company.name'));

        // $personAddresses = $request->input('person.addresses', []);
        // $personAddresses = $request->input('person.addresses', []);
        // dd($personAddresses);


        if ($hasPerson) {
            // اعتبارسنجی فیلدهای مشتری حقیقی
            $validated = $request->validate([
                'person.first_name' => 'required|string|max:190',
                'person.last_name' => 'required|string|max:190',
                'person.passport_number' => 'nullable|string|max:50|unique:customers,passport_number',
                'person.national_code' => 'required|digits:10|unique:customers,national_code',
                'person.birthdate' => 'nullable|date',
                'person.email' => 'nullable|email|max:190|unique:customers,email',
            ]);

            // print_r($validated);die();

            // اعتبارسنجی شماره همراه (حداقل یکی و یکتا بودن)
            $personAddresses = $request->input('person.addresses', []);
            $hasMobile = false;
            foreach ($personAddresses as $adr) {
                if (!empty($adr['contacts'])) {
                    foreach ($adr['contacts'] as $contact) {
                        if ($contact['type'] === 'mobile' && $contact['value'] != null) {
                            $hasMobile = true;
                            // چک تکراری بودن شماره موبایل در کل سیستم
                            if (Contact::where('value', $contact['value'])->exists()) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'person.addresses' => 'شماره همراه تکراری است!',
                                ]);
                            }
                        }
                    }
                }
            }

            

            if (!$hasMobile) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'person.addresses' => 'حداقل یک شماره همراه برای مشتری حقیقی لازم است.',
                ]);
            }

            

            // ذخیره مشتری
            $customer = Customer::create([
                'first_name'     => $request->person['first_name'],
                'last_name'      => $request->person['last_name'],
                'passport_number'=> $request->person['passport_number'],
                'national_code'  => $request->person['national_code'],
                'birthdate'      => $request->person['birthdate'],
                'email'          => $request->person['email'],
                // اضافه کردن سایر فیلدها اگر نیاز است
            ]);

            // ذخیره آدرس و تماس‌های هر آدرس
            foreach ($personAddresses as $address) {
                // آدرس را ذخیره کن
                $addr = $customer->addresses()->create([
                    'country_id' => $address['country_id'] ?? null,
                    'province_id'=> $address['province_id'] ?? null,
                    'city_id'    => $address['city_id'] ?? null,
                    'postal_code'    => $address['postal_code'] ?? '',
                    'address_detail' => $address['address_detail'] ?? '',
                ]);
                // تماس‌های مرتبط با این آدرس را ذخیره کن
                if (!empty($address['contacts'])) {
                    foreach ($address['contacts'] as $contact) {
                        if ($contact['value']) {
                            $addr->contacts()->create([
                                'type'  => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
        }


        $company = null;

        if ($hasCompany) {
            // اعتبارسنجی فیلدهای شرکت
            $validatedCompany = $request->validate([
                'company.name' => 'required|string|max:190|unique:companies,name',
                'company.economic_code' => 'required|string|max:50|unique:companies,economic_code',
                'company.registration_number' => 'required|string|max:50|unique:companies,registration_number',
                'company.email' => 'nullable|email|max:190|unique:companies,email',
            ]);

            // اعتبارسنجی شماره ثابت (حداقل یکی و یکتا بودن برای هر آدرس)
            $companyAddresses = $request->input('company.addresses', []);
            if (empty($companyAddresses)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'company.addresses' => 'حداقل باید یک آدرس برای شرکت وارد کنید.',
                ]);
            }
            foreach ($companyAddresses as $address) {
                $phoneExists = false;
                if (!empty($address['contacts'])) {
                    foreach ($address['contacts'] as $contact) {
                        if ($contact['type'] === 'phone') {
                            $phoneExists = true;
                            // چک تکرار شماره تلفن ثابت شرکت در کل system
                            if (Contact::where('value', $contact['value'])->exists()) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'company.addresses' => 'شماره تلفن شرکت تکراری است!',
                                ]);
                            }
                        }
                    }
                }
                if (!$phoneExists) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'company.addresses' => 'هر آدرس شرکت باید دست کم یک شماره تلفن ثابت داشته باشد.',
                    ]);
                }
            }

            // ثبت شرکت
            $company = Company::create([
                'name' => $request->company['name'],
                'economic_code' => $request->company['economic_code'],
                'registration_number' => $request->company['registration_number'],
                'email' => $request->company['email'],
                // سایر فیلدها
            ]);

            // ثبت آدرس و تماس شرکت
            foreach ($companyAddresses as $address) {
                $addr = $company->addresses()->create([
                    'country_id' => $address['country_id'] ?? null,
                    'province_id'=> $address['province_id'] ?? null,
                    'city_id'    => $address['city_id'] ?? null,
                    'postal_code'    => $address['postal_code'] ?? '',
                    'address_detail' => $address['address_detail'] ?? '',
                ]);
                if (!empty($address['contacts'])) {
                    foreach ($address['contacts'] as $contact) {
                        if ($contact['value']) {
                            $addr->contacts()->create([
                                'type' => $contact['type'],
                                'value' => $contact['value'],
                            ]);
                        }
                    }
                }
            }
        }

        if ($hasPerson && $hasCompany) {
            $customer->companies()->attach($company->id/*, ['position' => 'اختیاری'] */);
            // اگر چند نفر (کارمند) ثبت می‌کنی باید حلقه داشته باشی روی هر شخص و attach برای هر کدام صدا بزنی
        }



        // برای شرکت هم دقیقاً مثل بالا ولی با فیلدهای company و address_company و contact_company عمل کن

        return redirect()->route('customers.index')->with('success', 'مشتری با موفقیت ثبت شد!');
    }


    // public function show(Customer $customer)
    // {
    //     $customer->load('company.addresses.contacts', 'addresses.contacts');

    //     // $customer->load('company', 'addresses.contacts', 'addresses');
    //     return view('customers.show', compact('customer'));
    // }

    public function show(Customer $customer)
    {
        $customer->load('company.addresses.country','company.addresses.province','company.addresses.city',
                        'addresses.country','addresses.province','addresses.city','addresses.contacts');
        return view('customers.show', compact('customer'));
    }


    // public function edit(Customer $customer)
    // {
    //     $customer->load('company', 'addresses.contacts', 'addresses');
    //     $companies = Company::all();
    //     return view('customers.edit', compact('customer', 'companies'));
    // }


    public function edit(Customer $customer)
    {
        $customer->load('company.addresses.contacts','addresses.contacts',
                        'addresses.country','addresses.province','addresses.city',
                        'company.addresses.country','company.addresses.province','company.addresses.city');

        $companies = Company::all();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();

        return view('customers.edit', compact('customer', 'companies','countries'));
    }

    public function update(Request $request, Customer $customer)
    {
        $hasPerson = !empty($request->input('person.first_name'));
        $hasCompany = !empty($request->input('company.name'));

        if ($hasPerson) {
            $validated = $request->validate([
                'person.first_name'       => 'required|string|max:190',
                'person.last_name'        => 'required|string|max:190',
                'person.passport_number'  => 'nullable|string|max:50|unique:customers,passport_number,'.$customer->id,
                'person.national_code'    => 'required|digits:10|unique:customers,national_code,'.$customer->id,
                'person.birthdate'        => 'nullable|date',
                'person.email'            => 'nullable|email|max:190|unique:customers,email,'.$customer->id,
            ]);

            $customer->update([
                'first_name'      => $request->person['first_name'],
                'last_name'       => $request->person['last_name'],
                'passport_number' => $request->person['passport_number'],
                'national_code'   => $request->person['national_code'],
                'birthdate'       => $request->person['birthdate'],
                'email'           => $request->person['email'],
            ]);

            $incomingAddresses = $request->input('person.addresses', []);
            $existingAddresses = $customer->addresses()->get();

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

                    $contactsData = $adr['contacts'] ?? [];
                    $existingContacts = $existing->contacts()->get();
                    $contactIds = [];

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
                    $newAddress = $customer->addresses()->create([
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
            $customer->addresses()->whereNotIn('id', $incomingIds)->delete();
        }

        // ==== شرکت ====
        $company = null;
        if ($hasCompany) {
            $company = $customer->company ?? new \App\Models\Company();

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

                    $contactsData = $adr['contacts'] ?? [];
                    $existingContacts = $existing->contacts()->get();
                    $contactIds = [];

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

            if (!$customer->company_id || $customer->company_id !== $company->id) {
                $customer->company_id = $company->id;
                $customer->save();
            }
        }

        return redirect()->route('customers.index')->with('success', 'اطلاعات با موفقیت ویرایش شد!');
    }


    // public function update(Request $request, Customer $customer)
    // {
    //     $hasPerson = !empty($request->input('person.first_name'));
    //     $hasCompany = !empty($request->input('company.name'));

    //     if ($hasPerson) {
    //         $validated = $request->validate([
    //             'person.first_name'       => 'required|string|max:190',
    //             'person.last_name'        => 'required|string|max:190',
    //             'person.passport_number'  => 'nullable|string|max:50|unique:customers,passport_number,'.$customer->id,
    //             'person.national_code'    => 'required|digits:10|unique:customers,national_code,'.$customer->id,
    //             'person.birthdate'        => 'nullable|date',
    //             'person.email'            => 'nullable|email|max:190|unique:customers,email,'.$customer->id,
    //         ]);

    //         $customer->update([
    //             'first_name'      => $request->person['first_name'],
    //             'last_name'       => $request->person['last_name'],
    //             'passport_number' => $request->person['passport_number'],
    //             'national_code'   => $request->person['national_code'],
    //             'birthdate'       => $request->person['birthdate'],
    //             'email'           => $request->person['email'],
    //         ]);

    //         $incomingAddresses = $request->input('person.addresses', []);
    //         $existingAddresses = $customer->addresses()->get();

    //         $incomingIds = [];
    //         foreach ($incomingAddresses as $idx => $adr) {
    //             $addrId = $adr['id'] ?? null;
    //             // اگر id موجود بود update، اگر نبود create
    //             if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
    //                 $existing->update([
    //                     'country_id' => $address['country_id'] ?? null,
    //                     'province_id'=> $address['province_id'] ?? null,
    //                     'city_id'    => $address['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $addrId;

    //                 // sync contacts:
    //                 $contactsData = $adr['contacts'] ?? [];
    //                 $existingContacts = $existing->contacts()->get();
    //                 $contactIds = [];
    //                 foreach ($contactsData as $cK => $contact) {
    //                     $cId = $contact['id'] ?? null;
    //                     if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
    //                         $exContact->update([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $cId;
    //                     } elseif (!empty($contact['value'])) {
    //                         $newContact = $existing->contacts()->create([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $newContact->id;
    //                     }
    //                 }
    //                 // حذف شماره‌هایی که دیگر در فرم جدید نیستند:
    //                 $existing->contacts()->whereNotIn('id', $contactIds)->delete();

    //             } else {
    //                 // create new address + contacts
    //                 $newAddress = $customer->addresses()->create([
    //                     'country_id' => $address['country_id'] ?? null,
    //                     'province_id'=> $address['province_id'] ?? null,
    //                     'city_id'    => $address['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $newAddress->id;
    //                 foreach (($adr['contacts'] ?? []) as $contact) {
    //                     if (!empty($contact['value'])) {
    //                         $newAddress->contacts()->create([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //         // حذف آدرس‌هایی که در فرم نبودند:
    //         $customer->addresses()->whereNotIn('id', $incomingIds)->delete();
    //     }

    //     // ==== شرکت ====
    //     $company = null;
    //     if ($hasCompany) {
    //         $company = $customer->company ?? new \App\Models\Company();
    //         $validatedCompany = $request->validate([
    //             'company.name'               => 'required|string|max:190|unique:companies,name,' . ($company->id ?? 'NULL') . ',id',
    //             'company.economic_code'      => 'required|string|max:50|unique:companies,economic_code,' . ($company->id ?? 'NULL') . ',id',
    //             'company.registration_number'=> 'required|string|max:50|unique:companies,registration_number,' . ($company->id ?? 'NULL') . ',id',
    //             'company.email'              => 'nullable|email|max:190|unique:companies,email,' . ($company->id ?? 'NULL') . ',id',
    //         ]);
    //         $company->fill([
    //             'name' => $request->company['name'],
    //             'economic_code' => $request->company['economic_code'],
    //             'registration_number' => $request->company['registration_number'],
    //             'email' => $request->company['email'],
    //         ]);
    //         $company->save();

    //         // sync company addresses:
    //         $incomingAddresses = $request->input('company.addresses', []);
    //         $existingAddresses = $company->addresses()->get();
    //         $incomingIds = [];
    //         foreach ($incomingAddresses as $idx => $adr) {
    //             $addrId = $adr['id'] ?? null;
    //             if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
    //                 $existing->update([
    //                     'country_id' => $address['country_id'] ?? null,
    //                     'province_id'=> $address['province_id'] ?? null,
    //                     'city_id'    => $address['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $addrId;
    //                 // contacts sync (مشابه بالا)
    //                 $contactsData = $adr['contacts'] ?? [];
    //                 $existingContacts = $existing->contacts()->get();
    //                 $contactIds = [];
    //                 foreach ($contactsData as $cK => $contact) {
    //                     $cId = $contact['id'] ?? null;
    //                     if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
    //                         $exContact->update([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $cId;
    //                     } elseif (!empty($contact['value'])) {
    //                         $newContact = $existing->contacts()->create([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $newContact->id;
    //                     }
    //                 }
    //                 $existing->contacts()->whereNotIn('id', $contactIds)->delete();
    //             } else {
    //                 // new address
    //                 $newAddress = $company->addresses()->create([
    //                     'country_id' => $address['country_id'] ?? null,
    //                     'province_id'=> $address['province_id'] ?? null,
    //                     'city_id'    => $address['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $newAddress->id;
    //                 foreach (($adr['contacts'] ?? []) as $contact) {
    //                     if (!empty($contact['value'])) {
    //                         $newAddress->contacts()->create([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //         $company->addresses()->whereNotIn('id', $incomingIds)->delete();

    //         // حفظ ارتباط
    //         if (!$customer->company_id || $customer->company_id !== $company->id) {
    //             $customer->company_id = $company->id;
    //             $customer->save();
    //         }
    //     }

    //     return redirect()->route('customers.index')->with('success', 'اطلاعات با موفقیت ویرایش شد!');
    // }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'مشتری حذف شد!');
    }
}

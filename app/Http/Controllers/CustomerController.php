<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Person;
use App\Models\CustomerLink;
use App\Models\Contact;
use App\Models\Address;
use App\Models\Source;
use App\Models\CountryFaEn;
use App\Models\ProvinceFaEn;
use App\Models\CityFaEn;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
   public function index(Request $request)
    {
        // eager load برای جلوگیری از N+1
        $query = Customer::with([
            'persons.addresses.contacts',
            'persons.addresses.country',
            'companies.addresses.contacts',
            'companies.addresses.country',
        ]);

        // فیلتر نوع مشتری: حقیقی / حقوقی / هر دو
        if ($request->filled('type')) {
            $type = $request->input('type');

            if ($type === 'individual') {
                // فقط شخص، بدون شرکت
                $query->whereHas('persons')
                    ->whereDoesntHave('companies');
            } elseif ($type === 'company') {
                // فقط شرکت، بدون شخص
                $query->whereHas('companies')
                    ->whereDoesntHave('persons');
            } elseif ($type === 'both') {
                // هم شخص و هم شرکت
                $query->whereHas('persons')
                    ->whereHas('companies');
            }
        }

        // جستجو بر اساس نام / ایمیل (در شخص یا شرکت)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                // اشخاص
                $q->whereHas('persons', function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                // یا شرکت‌ها
                ->orWhereHas('companies', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // جستجو بر اساس کد ملی یا پاسپورت (فقط روی Person)
        if ($request->filled('code')) {
            $code = $request->input('code');

            $query->whereHas('persons', function ($q) use ($code) {
                $q->where('national_code', 'like', "%{$code}%")
                ->orWhere('passport_number', 'like', "%{$code}%");
            });
        }

        // فیلتر بر اساس شرکت
        if ($request->filled('company_id')) {
            $companyId = $request->input('company_id');

            $query->whereHas('companies', function ($q) use ($companyId) {
                $q->where('companies.id', $companyId);
            });
        }

        // فیلتر کشور
        if ($request->filled('country_id')) {
            $countryId = $request->input('country_id');

            $query->where(function ($q) use ($countryId) {
                $q->whereHas('persons.addresses', function ($sub) use ($countryId) {
                    $sub->where('country_id', $countryId);
                })->orWhereHas('companies.addresses', function ($sub) use ($countryId) {
                    $sub->where('country_id', $countryId);
                });
            });
        }

        // فیلتر استان
        if ($request->filled('province_id')) {
            $provinceId = $request->input('province_id');

            $query->where(function ($q) use ($provinceId) {
                $q->whereHas('persons.addresses', function ($sub) use ($provinceId) {
                    $sub->where('province_id', $provinceId);
                })->orWhereHas('companies.addresses', function ($sub) use ($provinceId) {
                    $sub->where('province_id', $provinceId);
                });
            });
        }

        // فیلتر شهر
        if ($request->filled('city_id')) {
            $cityId = $request->input('city_id');

            $query->where(function ($q) use ($cityId) {
                $q->whereHas('persons.addresses', function ($sub) use ($cityId) {
                    $sub->where('city_id', $cityId);
                })->orWhereHas('companies.addresses', function ($sub) use ($cityId) {
                    $sub->where('city_id', $cityId);
                });
            });
        }

        // جستجو بر اساس شماره تماس (در contacts آدرس‌ها)
        if ($request->filled('phone')) {
            $phone = $request->input('phone');

            $query->where(function ($q) use ($phone) {
                $q->whereHas('persons.addresses.contacts', function ($sub) use ($phone) {
                    $sub->where('value', 'like', "%{$phone}%");
                })->orWhereHas('companies.addresses.contacts', function ($sub) use ($phone) {
                    $sub->where('value', 'like', "%{$phone}%");
                });
            });
        }

        // مرتب‌سازی (فقط بر اساس فیلدهای Customer)
        $sortBy    = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (!in_array($sortBy, ['created_at', 'updated_at'])) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        // صفحه‌بندی
        $customers = $query->paginate(20);
        $customers->appends($request->except('page'));

        // داده‌های کمکی فیلتر
        $companies = Company::orderBy('name')->get();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        $provinces = collect(); // با JS پر می‌کنی
        $cities    = collect();

        return view('customers.index', compact('customers', 'companies', 'countries', 'provinces', 'cities'));
    }

    
    public function create()
    {
        $countries = CountryFaEn::where('status_cn', 1)
            ->orderBy('name_fa')
            ->get();

        $existingCompanies = Company::orderBy('name')->get();
        $existingPersons   = Person::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('customers.create', compact('countries', 'existingCompanies', 'existingPersons'));
    }


    // public function create()
    // {
    //     $companies = Company::all();
    //     $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
    //     return view('customers.create', compact('companies', 'countries'));
    // }

    // public function store(Request $request)
    // {
    //     $hasPerson = !empty($request->input('person.first_name'));
    //     $hasCompany = !empty($request->input('company.name'));

    //     if ($hasPerson) {
    //         // اعتبارسنجی فیلدهای مشتری حقیقی
    //         $validated = $request->validate([
    //             'person.first_name' => 'required|string|max:190',
    //             'person.last_name' => 'required|string|max:190',
    //             'person.passport_number' => 'nullable|string|max:50|unique:customers,passport_number',
    //             'person.national_code' => 'required|digits:10|unique:customers,national_code',
    //             'person.birthdate' => 'nullable|date',
    //             'person.email' => 'nullable|email|max:190|unique:customers,email',
    //         ]);

    //         // اعتبارسنجی شماره همراه (حداقل یکی و یکتا بودن)
    //         $personAddresses = $request->input('person.addresses', []);
    //         $hasMobile = false;
    //         foreach ($personAddresses as $adr) {
    //             if (!empty($adr['contacts'])) {
    //                 foreach ($adr['contacts'] as $contact) {
    //                     if ($contact['type'] === 'mobile' && $contact['value'] != null) {
    //                         $hasMobile = true;
    //                         // چک تکراری بودن شماره موبایل در کل سیستم
    //                         if (Contact::where('value', $contact['value'])->exists()) {
    //                             throw \Illuminate\Validation\ValidationException::withMessages([
    //                                 'person.addresses' => 'شماره همراه تکراری است!',
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         if (!$hasMobile) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 'person.addresses' => 'حداقل یک شماره همراه برای مشتری حقیقی لازم است.',
    //             ]);
    //         }

            

    //         // ذخیره مشتری
    //         $customer = Customer::create([
    //             'first_name'     => $request->person['first_name'],
    //             'last_name'      => $request->person['last_name'],
    //             'passport_number'=> $request->person['passport_number'],
    //             'national_code'  => $request->person['national_code'],
    //             'birthdate'      => $request->person['birthdate'],
    //             'email'          => $request->person['email'],
    //             // اضافه کردن سایر فیلدها اگر نیاز است
    //         ]);

    //         // ذخیره آدرس و تماس‌های هر آدرس
    //         foreach ($personAddresses as $address) {
    //             // آدرس را ذخیره کن
    //             $addr = $customer->addresses()->create([
    //                 'country_id' => $address['country_id'] ?? null,
    //                 'province_id'=> $address['province_id'] ?? null,
    //                 'city_id'    => $address['city_id'] ?? null,
    //                 'postal_code'    => $address['postal_code'] ?? '',
    //                 'address_detail' => $address['address_detail'] ?? '',
    //             ]);
    //             // تماس‌های مرتبط با این آدرس را ذخیره کن
    //             if (!empty($address['contacts'])) {
    //                 foreach ($address['contacts'] as $contact) {
    //                     if ($contact['value']) {
    //                         $addr->contacts()->create([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }


    //     $company = null;

    //     if ($hasCompany) {
    //         // اعتبارسنجی فیلدهای شرکت
    //         $validatedCompany = $request->validate([
    //             'company.name' => 'required|string|max:190|unique:companies,name',
    //             'company.economic_code' => 'required|string|max:50|unique:companies,economic_code',
    //             'company.registration_number' => 'required|string|max:50|unique:companies,registration_number',
    //             'company.email' => 'nullable|email|max:190|unique:companies,email',
    //         ]);

    //         // اعتبارسنجی شماره ثابت (حداقل یکی و یکتا بودن برای هر آدرس)
    //         $companyAddresses = $request->input('company.addresses', []);
    //         if (empty($companyAddresses)) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 'company.addresses' => 'حداقل باید یک آدرس برای شرکت وارد کنید.',
    //             ]);
    //         }
    //         foreach ($companyAddresses as $address) {
    //             $phoneExists = false;
    //             if (!empty($address['contacts'])) {
    //                 foreach ($address['contacts'] as $contact) {
    //                     if ($contact['type'] === 'phone') {
    //                         $phoneExists = true;
    //                         // چک تکرار شماره تلفن ثابت شرکت در کل system
    //                         if (Contact::where('value', $contact['value'])->exists()) {
    //                             throw \Illuminate\Validation\ValidationException::withMessages([
    //                                 'company.addresses' => 'شماره تلفن شرکت تکراری است!',
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }
    //             if (!$phoneExists) {
    //                 throw \Illuminate\Validation\ValidationException::withMessages([
    //                     'company.addresses' => 'هر آدرس شرکت باید دست کم یک شماره تلفن ثابت داشته باشد.',
    //                 ]);
    //             }
    //         }

    //         // ثبت شرکت
    //         $company = Company::create([
    //             'name' => $request->company['name'],
    //             'economic_code' => $request->company['economic_code'],
    //             'registration_number' => $request->company['registration_number'],
    //             'email' => $request->company['email'],
    //             // سایر فیلدها
    //         ]);

    //         // ثبت آدرس و تماس شرکت
    //         foreach ($companyAddresses as $address) {
    //             $addr = $company->addresses()->create([
    //                 'country_id' => $address['country_id'] ?? null,
    //                 'province_id'=> $address['province_id'] ?? null,
    //                 'city_id'    => $address['city_id'] ?? null,
    //                 'postal_code'    => $address['postal_code'] ?? '',
    //                 'address_detail' => $address['address_detail'] ?? '',
    //             ]);
    //             if (!empty($address['contacts'])) {
    //                 foreach ($address['contacts'] as $contact) {
    //                     if ($contact['value']) {
    //                         $addr->contacts()->create([
    //                             'type' => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     if ($hasPerson && $hasCompany) {
    //         $customer->companies()->attach($company->id/*, ['position' => 'اختیاری'] */);
    //         // اگر چند نفر (کارمند) ثبت می‌کنی باید حلقه داشته باشی روی هر شخص و attach برای هر کدام صدا بزنی
    //     }



    //     // برای شرکت هم دقیقاً مثل بالا ولی با فیلدهای company و address_company و contact_company عمل کن

    //     return redirect()->route('customers.index')->with('success', 'مشتری با موفقیت ثبت شد!');
    // }

    public function store(Request $request)
    {
        // 1) ولیدیشن ورودی
        $data = $request->validate([
            // اطلاعات کلی مشتری
            'source'         => ['nullable', 'in:website,instagram,telegram,business_partners,phone_marketing,from_employees,from_customers,word_of_mouth,public_relations,seminar,conference,exhibition,mass_advertising,email_marketing,sms_marketing,fax_marketing,direct_contact'],
            'is_active'      => ['nullable', 'boolean'],
            'customer_scope' => ['required', 'in:domestic,foreign'],

            // شخص جدید (آرایه person[...])
            'person.first_name'      => ['nullable', 'string', 'max:255'],
            'person.last_name'       => ['nullable', 'string', 'max:255'],
            'person.birthdate'       => ['nullable', 'date'],
            'person.national_code'   => ['nullable', 'string', 'max:20'],
            'person.passport_number' => ['nullable', 'string', 'max:50'],
            'person.email'           => ['nullable', 'email', 'max:255'],
            'person_mobile'          => ['nullable', 'string', 'max:50'],

            // شرکت جدید (آرایه company[...])
            'company.name'                => ['nullable', 'string', 'max:255'],
            'company.economic_code'       => ['nullable', 'string', 'max:50'],
            'company.registration_number' => ['nullable', 'string', 'max:50'],
            'company.email'               => ['nullable', 'email', 'max:255'],
            'company_phone'               => ['nullable', 'string', 'max:50'],

            // افراد قبلی برای اتصال به شرکت
            'existing_person_ids'   => ['nullable', 'array'],
            'existing_person_ids.*' => ['integer', 'exists:persons,id'],
            'existing_person_role'  => ['nullable', 'string', 'max:100'],

            // آدرس‌ها (اگر خواستی می‌توانی رویشان rule بگذاری)
            // 'person.addresses' => ['array'],
            // 'company.addresses' => ['array'],
        ]);

        $scope       = $data['customer_scope']; // domestic | foreign
        $personData  = $data['person']  ?? [];
        $companyData = $data['company'] ?? [];

        // تشخیص این‌که واقعاً شخص/شرکت داده دارند یا نه
        $hasPerson = !empty($personData['first_name'])
            || !empty($personData['last_name'])
            || !empty($data['person_mobile']);

        $hasCompany = !empty($companyData['name'])
            || !empty($data['company_phone']);

        if (! $hasPerson && ! $hasCompany) {
            return back()
                ->withErrors(['person.first_name' => 'حداقل باید اطلاعات شخص یا شرکت را وارد کنید.'])
                ->withInput();
        }

        // 2) چک یکتایی قبل از ساخت

        // شخص: داخلی → کد ملی، خارجی → پاسپورت
        if ($hasPerson) {
            if ($scope === 'domestic' && !empty($personData['national_code'])) {
                $existsNat = Person::where('national_code', $personData['national_code'])->exists();
                if ($existsNat) {
                    return back()
                        ->withErrors(['person.national_code' => 'شخصی با این کد ملی قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }

            if ($scope === 'foreign' && !empty($personData['passport_number'])) {
                $existsPass = Person::where('passport_number', $personData['passport_number'])->exists();
                if ($existsPass) {
                    return back()
                        ->withErrors(['person.passport_number' => 'شخصی با این شماره پاسپورت قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }

            if (!empty($personData['email'])) {
                $existsEmail = Person::where('email', $personData['email'])->exists();
                if ($existsEmail) {
                    return back()
                        ->withErrors(['person.email' => 'شخصی با این ایمیل قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }

            if (!empty($data['person_mobile'])) {
                $existsMob = Contact::where('type', 'mobile')->where('value', $data['person_mobile'])->exists();
                if ($existsMob) {
                    return back()
                        ->withErrors(['person_mobile' => 'این شماره موبایل قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }
        }

        // شرکت: فقط اگر داده‌ای دارد
        if ($hasCompany) {
            if ($scope === 'domestic') {
                if (!empty($companyData['economic_code'])) {
                    $existsEco = Company::where('economic_code', $companyData['economic_code'])->exists();
                    if ($existsEco) {
                        return back()
                            ->withErrors(['company.economic_code' => 'شرکتی با این کد اقتصادی قبلاً ثبت شده است.'])
                            ->withInput();
                    }
                }
                if (!empty($companyData['registration_number'])) {
                    $existsReg = Company::where('registration_number', $companyData['registration_number'])->exists();
                    if ($existsReg) {
                        return back()
                            ->withErrors(['company.registration_number' => 'شرکتی با این شماره ثبت قبلاً ثبت شده است.'])
                            ->withInput();
                    }
                }
            }

            if (!empty($companyData['email'])) {
                $existsCompanyEmail = Company::where('email', $companyData['email'])->exists();
                if ($existsCompanyEmail) {
                    return back()
                        ->withErrors(['company.email' => 'شرکتی با این ایمیل قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }

            if (!empty($data['company_phone'])) {
                $existsCompanyPhone = Contact::where('type', 'phone')->where('value', $data['company_phone'])->exists();
                if ($existsCompanyPhone) {
                    return back()
                        ->withErrors(['company_phone' => 'این شماره تلفن شرکت قبلاً ثبت شده است.'])
                        ->withInput();
                }
            }
        }

        // 3) ساخت Customer + Person + Company + لینک‌ها در تراکنش
        \DB::beginTransaction();

        try {
            // Customer مرکزی
            $customer = Customer::create([
                'source'    => $data['source'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'creator_id'=> auth()->id(),
            ]);

            $person  = null;
            $company = null;

            // ساخت شخص (اگر اطلاعات دارد)
            if ($hasPerson) {
                $person = Person::create([
                    'first_name'      => $personData['first_name']      ?? null,
                    'last_name'       => $personData['last_name']       ?? null,
                    'birthdate'       => $personData['birthdate']       ?? null,
                    'national_code'   => $scope === 'domestic' ? ($personData['national_code'] ?? null) : null,
                    'passport_number' => $scope === 'foreign'  ? ($personData['passport_number'] ?? null) : null,
                    'email'           => $personData['email']           ?? null,
                ]);

                // لینک Customer ↔ Person
                CustomerLink::create([
                    'customer_id'   => $customer->id,
                    'linkable_type' => Person::class,
                    'linkable_id'   => $person->id,
                ]);

                // تماس شخص
                // if (!empty($data['person_mobile'])) {
                //     Contact::create([
                //         'contactable_type' => Person::class,
                //         'contactable_id'   => $person->id,
                //         'type'             => 'mobile',
                //         'value'            => $data['person_mobile'],
                //     ]);
                // }
                // اگر تلفن ثابت جدا می‌خواهی:
                // if (!empty($data['person_phone'])) {
                //     Contact::create([
                //         'contactable_type' => Person::class,
                //         'contactable_id'   => $person->id,
                //         'type'             => 'phone',
                //         'value'            => $data['person_phone'],
                //     ]);
                // }

                // آدرس‌های شخص (اگر در فرم آمده باشد)
                if (!empty($request->input('person.addresses'))) {
                    foreach ($request->input('person.addresses') as $addr) {
                        if (empty($addr['address_detail']) &&
                            empty($addr['country_id']) &&
                            empty($addr['province_id']) &&
                            empty($addr['city_id'])) {
                            continue;
                        }

                        $address = Address::create([
                            'addressable_type' => Person::class,
                            'addressable_id'   => $person->id,
                            'country_id'       => $addr['country_id']     ?? null,
                            'province_id'      => $addr['province_id']    ?? null,
                            'city_id'          => $addr['city_id']        ?? null,
                            'address_detail'   => $addr['address_detail'] ?? null,
                        ]);

                        if (!empty($addr['contacts']) && is_array($addr['contacts'])) {
                            foreach ($addr['contacts'] as $contact) {
                                if (empty($contact['value'])) {
                                    continue;
                                }

                                Contact::create([
                                    'address_id' => $address->id,
                                    'type'       => $contact['type']  ?? 'phone',
                                    'value'      => $contact['value'],
                                ]);
                            }
                        }
                    }
                }

            }

            // ساخت شرکت (اگر اطلاعات دارد)
            if ($hasCompany) {
                $company = Company::create([
                    'name'               => $companyData['name']                ?? null,
                    'economic_code'      => $scope === 'domestic' ? ($companyData['economic_code'] ?? null) : null,
                    'registration_number'=> $scope === 'domestic' ? ($companyData['registration_number'] ?? null) : null,
                    'email'              => $companyData['email']              ?? null,
                ]);

                // لینک Customer ↔ Company
                CustomerLink::create([
                    'customer_id'   => $customer->id,
                    'linkable_type' => Company::class,
                    'linkable_id'   => $company->id,
                ]);

                // تماس شرکت
                // if (!empty($data['company_phone'])) {
                //     Contact::create([
                //         'contactable_type' => Company::class,
                //         'contactable_id'   => $company->id,
                //         'type'             => 'phone',
                //         'value'            => $data['company_phone'],
                //     ]);
                // }

                // آدرس‌های شرکت
                if (!empty($request->input('company.addresses'))) {
                    foreach ($request->input('company.addresses') as $addr) {
                        if (empty($addr['address_detail']) &&
                            empty($addr['country_id']) &&
                            empty($addr['province_id']) &&
                            empty($addr['city_id'])) {
                            continue;
                        }

                        $address = Address::create([
                            'addressable_type' => Company::class,
                            'addressable_id'   => $company->id,
                            'country_id'       => $addr['country_id']     ?? null,
                            'province_id'      => $addr['province_id']    ?? null,
                            'city_id'          => $addr['city_id']        ?? null,
                            'address_detail'   => $addr['address_detail'] ?? null,
                        ]);

                        if (!empty($addr['contacts']) && is_array($addr['contacts'])) {
                            foreach ($addr['contacts'] as $contact) {
                                if (empty($contact['value'])) {
                                    continue;
                                }

                                Contact::create([
                                    'address_id' => $address->id,
                                    'type'       => $contact['type']  ?? 'phone',
                                    'value'      => $contact['value'],
                                ]);
                            }
                        }
                    }
                }


                // اگر شخص جدید هم ساخته شده، می‌توان نقش او را در این شرکت ثبت کرد
                if ($person) {
                    CompanyCustomerRole::create([
                        'company_id' => $company->id,
                        'person_id'  => $person->id,
                        'role'       => $data['existing_person_role'] ?? null,
                    ]);
                }

                // افزودن افراد قبلی به این شرکت
                if (!empty($data['existing_person_ids'])) {
                    foreach ($data['existing_person_ids'] as $personId) {
                        CompanyCustomerRole::firstOrCreate(
                            [
                                'company_id' => $company->id,
                                'person_id'  => $personId,
                            ],
                            [
                                'role' => $data['existing_person_role'] ?? null,
                            ]
                        );
                    }
                }
            }

            \DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'مشتری با موفقیت ثبت شد.');
        } catch (\Throwable $e) {

             dd($e->getMessage(), $e->getFile(), $e->getLine());

            \DB::rollBack();
            report($e);
            
            return back()
                ->withErrors(['general' => 'خطایی در ثبت مشتری رخ داد.'])
                ->withInput();
        }
    }




    public function show(Customer $customer)
    {
        $customer->load([
            'persons.addresses.country',
            'persons.addresses.province',
            'persons.addresses.city',
            'persons.addresses.contacts',
            'companies.addresses.country',
            'companies.addresses.province',
            'companies.addresses.city',
            'companies.addresses.contacts',
        ]);

        return view('customers.show', compact('customer'));
    }


    // public function edit(Customer $customer)
    // {
    //     $customer->load('company.addresses.contacts','addresses.contacts',
    //                     'addresses.country','addresses.province','addresses.city',
    //                     'company.addresses.country','company.addresses.province','company.addresses.city');

    //     $companies = Company::all();
    //     $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();

    //     return view('customers.edit', compact('customer', 'companies','countries'));
    // }

    public function edit(Customer $customer)
    {
        $customer->load([
            'persons.addresses.contacts',
            'persons.addresses.country',
            'persons.addresses.province',
            'persons.addresses.city',
            'companies.addresses.contacts',
            'companies.addresses.country',
            'companies.addresses.province',
            'companies.addresses.city',
        ]);

        $companies = Company::orderBy('name')->get();
        $countries = CountryFaEn::where('status_cn', 1)->orderBy('name_fa')->get();
        $sources = Source::orderBy('id')->get(); // نام مدل/جدول را مطابق پروژه‌ات بگذار
        return view('customers.edit', compact('customer', 'companies', 'countries', 'sources'));
    }


    public function update(Request $request, Customer $customer)
    {
        // 1) ولیدیشن عمومی Customer + شخص/شرکت
        $data = $request->validate([
            // کلیات مشتری
            'customer_scope' => ['required', 'in:domestic,foreign'],
            'source'         => ['nullable', 'in:website,instagram,telegram,business_partners,phone_marketing,from_employees,from_customers,word_of_mouth,public_relations,seminar,conference,exhibition,mass_advertising,email_marketing,sms_marketing,fax_marketing,direct_contact'],
            'is_active'      => ['nullable', 'boolean'],

            // شخص
            'person.first_name'      => ['nullable', 'string', 'max:190'],
            'person.last_name'       => ['nullable', 'string', 'max:190'],
            'person.birthdate'       => ['nullable', 'date'],
            'person.national_code'   => ['nullable', 'string', 'max:20'],
            'person.passport_number' => ['nullable', 'string', 'max:50'],
            'person.email'           => ['nullable', 'email', 'max:190'],

            // شرکت
            'company.name'                => ['nullable', 'string', 'max:190'],
            'company.economic_code'       => ['nullable', 'string', 'max:50'],
            'company.registration_number' => ['nullable', 'string', 'max:50'],
            'company.email'               => ['nullable', 'email', 'max:190'],

            // آرایه آدرس‌ها (برای دیباگ ساده در اینجا فقط شکل را چک می‌کنیم)
            'person.addresses'   => ['nullable', 'array'],
            'company.addresses'  => ['nullable', 'array'],
        ]);

        $scope      = $data['customer_scope'];
        $personData = $data['person']   ?? [];
        $compData   = $data['company']  ?? [];

        $hasPerson  = !empty($personData['first_name']) || !empty($personData['last_name']);
        $hasCompany = !empty($compData['name']);

        if (! $hasPerson && ! $hasCompany) {
            return back()
                ->withErrors(['person.first_name' => 'حداقل باید اطلاعات شخص یا شرکت را وارد کنید.'])
                ->withInput();
        }

        // 2) چک یکتایی‌ها (با در نظر گرفتن خود رکورد فعلی)
        // نیاز داریم person و company فعلی را لود کنیم
        $customer->load(['persons', 'companies']);
        $person  = $customer->persons->first();
        $company = $customer->companies->first();

        // شخص
        if ($hasPerson) {
            if ($scope === 'domestic' && !empty($personData['national_code'])) {
                $existsNat = \App\Models\Person::where('national_code', $personData['national_code'])
                    ->when($person, fn($q) => $q->where('id', '!=', $person->id))
                    ->exists();
                if ($existsNat) {
                    return back()
                        ->withErrors(['person.national_code' => 'شخص دیگری با این کد ملی ثبت شده است.'])
                        ->withInput();
                }
            }

            if ($scope === 'foreign' && !empty($personData['passport_number'])) {
                $existsPass = \App\Models\Person::where('passport_number', $personData['passport_number'])
                    ->when($person, fn($q) => $q->where('id', '!=', $person->id))
                    ->exists();
                if ($existsPass) {
                    return back()
                        ->withErrors(['person.passport_number' => 'شخص دیگری با این شماره پاسپورت ثبت شده است.'])
                        ->withInput();
                }
            }

            if (!empty($personData['email'])) {
                $existsEmail = \App\Models\Person::where('email', $personData['email'])
                    ->when($person, fn($q) => $q->where('id', '!=', $person->id))
                    ->exists();
                if ($existsEmail) {
                    return back()
                        ->withErrors(['person.email' => 'شخص دیگری با این ایمیل ثبت شده است.'])
                        ->withInput();
                }
            }
        }

        // شرکت
        if ($hasCompany) {
            if ($scope === 'domestic') {
                if (!empty($compData['economic_code'])) {
                    $existsEco = \App\Models\Company::where('economic_code', $compData['economic_code'])
                        ->when($company, fn($q) => $q->where('id', '!=', $company->id))
                        ->exists();
                    if ($existsEco) {
                        return back()
                            ->withErrors(['company.economic_code' => 'شرکت دیگری با این کد اقتصادی ثبت شده است.'])
                            ->withInput();
                    }
                }
                if (!empty($compData['registration_number'])) {
                    $existsReg = \App\Models\Company::where('registration_number', $compData['registration_number'])
                        ->when($company, fn($q) => $q->where('id', '!=', $company->id))
                        ->exists();
                    if ($existsReg) {
                        return back()
                            ->withErrors(['company.registration_number' => 'شرکت دیگری با این شماره ثبت ثبت شده است.'])
                            ->withInput();
                    }
                }
            }

            if (!empty($compData['email'])) {
                $existsCompEmail = \App\Models\Company::where('email', $compData['email'])
                    ->when($company, fn($q) => $q->where('id', '!=', $company->id))
                    ->exists();
                if ($existsCompEmail) {
                    return back()
                        ->withErrors(['company.email' => 'شرکت دیگری با این ایمیل ثبت شده است.'])
                        ->withInput();
                }
            }
        }

        \DB::beginTransaction();

        try {
            // 3) به‌روزرسانی خود Customer
            $customer->update([
                'customer_scope' => $scope,
                'source'         => $data['source'] ?? null,
                'is_active'      => !empty($data['is_active']),
            ]);

            // 4) به‌روزرسانی/ساخت Person
            if ($hasPerson) {
                if (! $person) {
                    $person = \App\Models\Person::create([]);
                    // لینک Customer ↔ Person اگر قبلاً نبود
                    \App\Models\CustomerLink::firstOrCreate([
                        'customer_id'   => $customer->id,
                        'linkable_type' => \App\Models\Person::class,
                        'linkable_id'   => $person->id,
                    ]);
                }

                $person->update([
                    'first_name'      => $personData['first_name'] ?? null,
                    'last_name'       => $personData['last_name'] ?? null,
                    'birthdate'       => $personData['birthdate'] ?? null,
                    'national_code'   => $scope === 'domestic' ? ($personData['national_code'] ?? null) : null,
                    'passport_number' => $scope === 'foreign'  ? ($personData['passport_number'] ?? null) : null,
                    'email'           => $personData['email'] ?? null,
                ]);

                // آدرس‌ها و contacts شخص
                $incomingAddresses = $request->input('person.addresses', []);
                $existingAddresses = $person->addresses()->with('contacts')->get();
                $incomingIds       = [];

                foreach ($incomingAddresses as $adr) {
                    $addrId = $adr['id'] ?? null;

                    // اگر آدرس موجود
                    if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
                        $existing->update([
                            'country_id'     => $adr['country_id']     ?? null,
                            'province_id'    => $adr['province_id']    ?? null,
                            'city_id'        => $adr['city_id']        ?? null,
                            'postal_code'    => $adr['postal_code']    ?? '',
                            'address_detail' => $adr['address_detail'] ?? '',
                        ]);
                        $incomingIds[] = $addrId;

                        // contacts
                        $contactsData     = $adr['contacts'] ?? [];
                        $existingContacts = $existing->contacts;
                        $contactIds       = [];

                        foreach ($contactsData as $contact) {
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
                        // آدرس جدید
                        if (empty($adr['country_id']) &&
                            empty($adr['province_id']) &&
                            empty($adr['city_id']) &&
                            empty($adr['address_detail'])) {
                            continue;
                        }

                        $newAddress = $person->addresses()->create([
                            'country_id'     => $adr['country_id']     ?? null,
                            'province_id'    => $adr['province_id']    ?? null,
                            'city_id'        => $adr['city_id']        ?? null,
                            'postal_code'    => $adr['postal_code']    ?? '',
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

                // حذف آدرس‌های حذف‌شده
                if (!empty($incomingIds)) {
                    $person->addresses()->whereNotIn('id', $incomingIds)->delete();
                } else {
                    // اگر هیچ آدرسی نفرستاده، همه را حذف نکنیم؛ بسته به سیاستت می‌توانی این را تغییر دهی
                }
            }

            // 5) به‌روزرسانی/ساخت Company
            if ($hasCompany) {
                if (! $company) {
                    $company = \App\Models\Company::create([]);
                    \App\Models\CustomerLink::firstOrCreate([
                        'customer_id'   => $customer->id,
                        'linkable_type' => \App\Models\Company::class,
                        'linkable_id'   => $company->id,
                    ]);
                }

                $company->update([
                    'name'               => $compData['name']                ?? null,
                    'economic_code'      => $scope === 'domestic' ? ($compData['economic_code'] ?? null)      : null,
                    'registration_number'=> $scope === 'domestic' ? ($compData['registration_number'] ?? null): null,
                    'email'              => $compData['email']               ?? null,
                ]);

                // آدرس‌ها و contacts شرکت
                $incomingAddresses = $request->input('company.addresses', []);
                $existingAddresses = $company->addresses()->with('contacts')->get();
                $incomingIds       = [];

                foreach ($incomingAddresses as $adr) {
                    $addrId = $adr['id'] ?? null;

                    if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
                        $existing->update([
                            'country_id'     => $adr['country_id']     ?? null,
                            'province_id'    => $adr['province_id']    ?? null,
                            'city_id'        => $adr['city_id']        ?? null,
                            'postal_code'    => $adr['postal_code']    ?? '',
                            'address_detail' => $adr['address_detail'] ?? '',
                        ]);
                        $incomingIds[] = $addrId;

                        $contactsData     = $adr['contacts'] ?? [];
                        $existingContacts = $existing->contacts;
                        $contactIds       = [];

                        foreach ($contactsData as $contact) {
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
                        if (empty($adr['country_id']) &&
                            empty($adr['province_id']) &&
                            empty($adr['city_id']) &&
                            empty($adr['address_detail'])) {
                            continue;
                        }

                        $newAddress = $company->addresses()->create([
                            'country_id'     => $adr['country_id']     ?? null,
                            'province_id'    => $adr['province_id']    ?? null,
                            'city_id'        => $adr['city_id']        ?? null,
                            'postal_code'    => $adr['postal_code']    ?? '',
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

                if (!empty($incomingIds)) {
                    $company->addresses()->whereNotIn('id', $incomingIds)->delete();
                }
            }

            \DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'اطلاعات با موفقیت ویرایش شد!');
        } catch (\Throwable $e) {
            \DB::rollBack();
            report($e);

            return back()
                ->withErrors(['general' => 'خطایی در ویرایش مشتری رخ داد.'])
                ->withInput();
        }
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

    //             if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
    //                 $existing->update([
    //                     'country_id'     => $adr['country_id'] ?? null,
    //                     'province_id'    => $adr['province_id'] ?? null,
    //                     'city_id'        => $adr['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $addrId;

    //                 $contactsData = $adr['contacts'] ?? [];
    //                 $existingContacts = $existing->contacts()->get();
    //                 $contactIds = [];

    //                 foreach ($contactsData as $cK => $contact) {
    //                     $cId = $contact['id'] ?? null;
    //                     if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
    //                         $exContact->update([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $cId;
    //                     } elseif (!empty($contact['value'])) {
    //                         $newContact = $existing->contacts()->create([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $newContact->id;
    //                     }
    //                 }
    //                 $existing->contacts()->whereNotIn('id', $contactIds)->delete();

    //             } else {
    //                 $newAddress = $customer->addresses()->create([
    //                     'country_id'     => $adr['country_id'] ?? null,
    //                     'province_id'    => $adr['province_id'] ?? null,
    //                     'city_id'        => $adr['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $newAddress->id;

    //                 foreach (($adr['contacts'] ?? []) as $contact) {
    //                     if (!empty($contact['value'])) {
    //                         $newAddress->contacts()->create([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
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
    //             'name'               => $request->company['name'],
    //             'economic_code'      => $request->company['economic_code'],
    //             'registration_number'=> $request->company['registration_number'],
    //             'email'              => $request->company['email'],
    //         ]);
    //         $company->save();

    //         $incomingAddresses = $request->input('company.addresses', []);
    //         $existingAddresses = $company->addresses()->get();
    //         $incomingIds = [];

    //         foreach ($incomingAddresses as $idx => $adr) {
    //             $addrId = $adr['id'] ?? null;

    //             if ($addrId && $existing = $existingAddresses->firstWhere('id', $addrId)) {
    //                 $existing->update([
    //                     'country_id'     => $adr['country_id'] ?? null,
    //                     'province_id'    => $adr['province_id'] ?? null,
    //                     'city_id'        => $adr['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $addrId;

    //                 $contactsData = $adr['contacts'] ?? [];
    //                 $existingContacts = $existing->contacts()->get();
    //                 $contactIds = [];

    //                 foreach ($contactsData as $cK => $contact) {
    //                     $cId = $contact['id'] ?? null;
    //                     if ($cId && $exContact = $existingContacts->firstWhere('id', $cId)) {
    //                         $exContact->update([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $cId;
    //                     } elseif (!empty($contact['value'])) {
    //                         $newContact = $existing->contacts()->create([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                         $contactIds[] = $newContact->id;
    //                     }
    //                 }
    //                 $existing->contacts()->whereNotIn('id', $contactIds)->delete();

    //             } else {
    //                 $newAddress = $company->addresses()->create([
    //                     'country_id'     => $adr['country_id'] ?? null,
    //                     'province_id'    => $adr['province_id'] ?? null,
    //                     'city_id'        => $adr['city_id'] ?? null,
    //                     'postal_code'    => $adr['postal_code'] ?? '',
    //                     'address_detail' => $adr['address_detail'] ?? '',
    //                 ]);
    //                 $incomingIds[] = $newAddress->id;

    //                 foreach (($adr['contacts'] ?? []) as $contact) {
    //                     if (!empty($contact['value'])) {
    //                         $newAddress->contacts()->create([
    //                             'type'  => $contact['type'],
    //                             'value' => $contact['value'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }

    //         $company->addresses()->whereNotIn('id', $incomingIds)->delete();

    //         if (!$customer->company_id || $customer->company_id !== $company->id) {
    //             $customer->company_id = $company->id;
    //             $customer->save();
    //         }
    //     }

    //     return redirect()->route('customers.index')->with('success', 'اطلاعات با موفقیت ویرایش شد!');
    // }


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

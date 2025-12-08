<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Company;
use App\Models\CustomerLink;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'economic_code'      => ['nullable', 'string', 'max:50'],
            'registration_number'=> ['nullable', 'string', 'max:50'],
            'email'              => ['nullable', 'email'],
            'source'             => ['nullable', 'in:website,instagram,telegram,business_partners,phone_marketing,from_employees,from_customers,word_of_mouth,public_relations,seminar,conference,exhibition,mass_advertising,email_marketing,sms_marketing,fax_marketing,direct_contact'],
        ]);

        $company = Company::create([
            'name'               => $data['name'],
            'economic_code'      => $data['economic_code'] ?? null,
            'registration_number'=> $data['registration_number'] ?? null,
            'email'              => $data['email'] ?? null,
        ]);

        $customer = Customer::create([
            'source'    => $data['source'] ?? null,
            'is_active' => true,
            'creator_id'=> auth()->id(),
        ]);

        CustomerLink::create([
            'customer_id'   => $customer->id,
            'linkable_type' => Company::class,
            'linkable_id'   => $company->id,
        ]);

        return redirect()->route('companies.index')
            ->with('success', 'شرکت و مشتری مربوطه با موفقیت ایجاد شدند.');
    }

}

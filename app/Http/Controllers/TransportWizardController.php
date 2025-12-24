<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\PreInvoiceItem;
use App\Models\Transport;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


use App\Models\TransportVehicle;
use App\Models\TransportVehicleFile;
use App\Models\TransportVehicleItem;

use App\Enums\TransportStatus;
use App\Enums\TransportVehicleStatus;

use App\Enums\FreightAccountingStatus;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;




class TransportWizardController extends Controller
{
    
    public function show(PreInvoice $preInvoice, Transport $transport)
    {
        $user = auth()->user();

        if (!$user) {
            $role = 'guest';
        } else {
            $primaryRole = $user->roles()->pluck('name')->first();
            $role = $primaryRole ?? 'sales_expert';
        }

        $transport->load([
            'preInvoice.customer.persons.addresses.country',
            'preInvoice.customer.persons.addresses.province',
            'preInvoice.customer.persons.addresses.city',
            'preInvoice.customer.persons.addresses.contacts',
            'preInvoice.customer.companies.addresses.country',
            'preInvoice.customer.companies.addresses.province',
            'preInvoice.customer.companies.addresses.city',
            'preInvoice.customer.companies.addresses.contacts',
            'loadings.items.product',
            'vehicles.items.product',
            'vehicles.files',
        ]);

        $customer      = $transport->preInvoice?->customer;
        $customerScope = $customer?->customer_scope;
        $person        = $customer?->primaryPerson();
        $company       = $customer?->primaryCompany();
        $isPerson      = (bool) $person && ! $company;
        $isCompany     = (bool) $company;

        $addr = null;
        if ($company && $company->addresses->isNotEmpty()) {
            $addr = $company->addresses->first();
        } elseif ($person && $person->addresses->isNotEmpty()) {
            $addr = $person->addresses->first();
        }

        $countryName  = $addr?->country?->name_fa ?? null;
        $provinceName = $addr?->province?->name_fa ?? null;
        $cityName     = $addr?->city?->name_fa ?? null;

        $primaryRole = $user->roles()->pluck('name')->first();
        $role = $primaryRole ?? 'sales_expert';

        [$currentStepIndex, $allowedSteps] = $this->resolveSteps($transport, $role);

        $logisticsExperts = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['logistics_expert', 'logistics_manager']);
            })
            ->with('roles')
            ->orderBy('name')
            ->get();

        $isSuperAdmin = $user->isSuperAdmin();
        if ($isSuperAdmin) {
            foreach ($allowedSteps as $k => $v) {
                $allowedSteps[$k] = true;
            }
        }

        $addr       = $person?->addresses?->first();
        $countryName= $addr?->country?->name_fa ?? null;
        $provinceName= $addr?->province?->name_fa ?? null;
        $cityName   = $addr?->city?->name_fa ?? null;

        $preInvoiceProducts = PreInvoiceItem::query()
            ->where('pre_invoice_id', $preInvoice->id)
            ->with([
                'product',
                'chosenPurchaseAssignment.source.company',
                'chosenPurchaseAssignment.source.addresses.contacts',
                'chosenPurchaseAssignment.buyer',
            ])
            ->get();

        $allowedSteps     = $this->resolveAllowedSteps($user, $transport);
        $currentStepIndex = $this->resolveCurrentStepIndex($transport);

        // ساخت آرایه trucks با model داخلش
        $truckVehicles = $transport->vehicles
            ->where('is_wagon', false)
            ->values();

        $trucks = [];
        foreach ($truckVehicles as $index => $vehicle) {
            $trucks[] = [
                'model'                => $vehicle,                 // خود مدل
                'id'                   => $vehicle->id,
                'vehicle_type'         => $vehicle->vehicle_type,
                'freight_company_name' => $vehicle->freight_company_name,
                'driver_name'          => $vehicle->driver_name,
                'driver_national_code' => $vehicle->driver_national_code,
                'driver_mobile'        => $vehicle->driver_mobile,
                'driver_helper'        => $vehicle->driver_helper,
                'plate_iran'           => $vehicle->plate_iran,
                'plate_3digit'         => $vehicle->plate_3digit,
                'plate_letter'         => $vehicle->plate_letter,
                'plate_2digit'         => $vehicle->plate_2digit,
                'bill_of_lading_number'=> $vehicle->bill_of_lading_number,
                'planned_loading_at'   => optional($vehicle->planned_loading_at)->format('Y-m-d H:i'),
                'actual_loading_at'    => optional($vehicle->actual_loading_at)->format('Y-m-d H:i'),
                'arrival_at'           => optional($vehicle->arrival_at)->format('Y-m-d H:i'),
                'unloading_at'         => optional($vehicle->unloading_at)->format('Y-m-d H:i'),
                'total_freight_amount' => $vehicle->total_freight_amount,
                'loading_cost'         => $vehicle->loading_cost,
                'return_amount'        => $vehicle->return_amount,
                'description'          => $vehicle->description,
                'status'               => $vehicle->status?->value ?? $vehicle->status,
                'items'                => $vehicle->items,
                'files'                => $vehicle->files,
                'freight_accounting_status' => $vehicle->freight_accounting_status ?? null,
                'freight_reject_reason'     => $vehicle->freight_reject_reason ?? null,
            ];
        }

        // واگن‌ها
        $wagonVehicles = $transport->vehicles
            ->where('is_wagon', true)
            ->values();

        $wagons = [];
        foreach ($wagonVehicles as $index => $vehicle) {
            $wagons[] = [
                'model'                => $vehicle,
                'id'                   => $vehicle->id,
                'vehicle_type'         => $vehicle->vehicle_type,
                'freight_company_name' => $vehicle->freight_company_name,
                'planned_loading_at'   => optional($vehicle->planned_loading_at)->format('Y-m-d H:i'),
                'actual_loading_at'    => optional($vehicle->actual_loading_at)->format('Y-m-d H:i'),
                'arrival_at'           => optional($vehicle->arrival_at)->format('Y-m-d H:i'),
                'unloading_at'         => optional($vehicle->unloading_at)->format('Y-m-d H:i'),
                'wagon_cost'           => $vehicle->wagon_cost,
                'wagon_coordinator_mobile' => $vehicle->wagon_coordinator_mobile,
                'wagon_contact_phone'  => $vehicle->wagon_contact_phone,
                'bill_of_lading_number'=> $vehicle->bill_of_lading_number,
                'description'          => $vehicle->description,
                'status'               => $vehicle->status?->value ?? $vehicle->status,
                'items'                => $vehicle->items,
                'files'                => $vehicle->files,
                'freight_accounting_status' => $vehicle->freight_accounting_status ?? null,
                'freight_reject_reason'     => $vehicle->freight_reject_reason ?? null,
            ];
        }

        return view('transports.wizard', compact(
            'preInvoice',
            'transport',
            'allowedSteps',
            'currentStepIndex',
            'preInvoiceProducts',
            'customer',
            'customerScope',
            'person',
            'company',
            'isPerson',
            'isCompany',
            'countryName',
            'provinceName',
            'cityName',
            'role',
            'currentStepIndex',
            'allowedSteps',
            'logisticsExperts',
            'trucks',
            'wagons',
        ));
    }

    /**
     * تعیین stepهای مجاز و index شروع بر اساس status و نقش.
     */
    protected function resolveSteps(Transport $transport, string $role): array
    {
        $status = $transport->status instanceof TransportStatus
            ? $transport->status->value
            : $transport->status;

        $steps = [
            'sales'             => false,
            'purchase'          => false,
            'logistics_manager' => false,
            'logistics_expert'  => false,
            'accounting'        => false,
            'sales_manager'     => false,
            'logistics_close'   => false,
        ];
        // dd($status);

        // ترتیب جریان
        if ($status === TransportStatus::RequestedBySales->value) {
            $steps['sales'] = true;
        } elseif ($status === TransportStatus::CompletedBySales->value) {
            $steps['purchase'] = true;
        } elseif ($status === TransportStatus::CompletedByPurchase->value) {
            $steps['logistics_manager'] = true;
        } elseif ($status === TransportStatus::AssignedToLogistics->value || $status === TransportStatus::InProgressByTransport->value) {
            $steps['logistics_expert'] = true;
        } elseif ($status === TransportStatus::LogisticsCompleted->value) {
            $steps['accounting'] = true;
        } elseif ($status === TransportStatus::AccountingApproved->value) {
            $steps['sales_manager'] = true;
        } elseif ($status === TransportStatus::SalesManagerApproved->value) {
            $steps['logistics_close'] = true;
        }

        // dd($steps);

        $mapStepToIndex = [
            'sales'             => 0,
            'purchase'          => 1,
            'logistics_manager' => 2,
            'logistics_expert'  => 3,
            'accounting'        => 4,
            'sales_manager'     => 5,
            'logistics_close'   => 6,
        ];

        // $startStepKey = match ($role) {
        //     'sales_expert'        => 'sales',
        //     'purchase_expert'     => 'purchase',
        //     'logistics_manager'   => 'logistics_manager',
        //     'logistics_expert'    => 'logistics_expert',
        //     'accountant'          => 'accounting',
        //     'sales_manager'       => 'sales_manager',
        //     'logistics_admin'     => 'logistics_close',
        //     default               => 'sales',
        // };

        $startStepKey = match ($role) {
            'sales_expert'        => 'sales',
            'purchase_expert'     => 'purchase',
            'logistics_manager'   => 'logistics_manager',
            'logistics_expert'    => 'logistics_expert',
            'accountant'          => 'accounting',
            'sales_manager'       => 'sales_manager',
            'logistics_admin'     => 'logistics_close',
            default               => 'sales',
        };


        $currentStepIndex = $mapStepToIndex[$startStepKey] ?? 0;

        return [$currentStepIndex, $steps];
    }

    
    protected function resolveAllowedSteps(User $user, Transport $transport): array
    {
        $role = $user->roles()->pluck('name')->first();

        return [
            'sales'             => in_array($role, ['sales_expert', 'sales_manager']) || $user->isSuperAdmin(),
            'purchase'          => in_array($role, ['purchase_expert']) || $user->isSuperAdmin(),
            'logistics_manager' => in_array($role, ['logistics_manager']) || $user->isSuperAdmin(),
            'logistics_expert'  => in_array($role, ['logistics_expert', 'logistics_manager', ]) || $user->isSuperAdmin(),
            'accounting'        => in_array($role, ['accountant']) || $user->isSuperAdmin(),
            'sales_manager'     => in_array($role, ['sales_manager']) || $user->isSuperAdmin(),
            'close'             => $user->isSuperAdmin(),
        ];
    }

    protected function resolveCurrentStepIndex(Transport $transport): int
    {
        // اگر از enum TransportStatus استفاده می‌کنی:
        $status = $transport->status ? $transport->status->value : null;
        // dd($status);
        // مپ وضعیت‌ها به index گام‌ها (۰ = فروش، ۱ = خرید، ۲ = مدیر لجستیک، ...)
        return match ($status) {
            'requested_by_sales'       => 0,
            'completed_by_sales'       => 1,
            'completed_by_purchase'    => 2,
            'in_progress'              => 3,
            'completed_by_logistics'   => 4,
            'approved_by_accounting'   => 5,
            'approved_by_sales_manager'=> 6,
            'closed'                   => 6,
            default                    => 0,
        };
    }


    

    /**
     * مرحله ۱: کارشناس فروش
     */
    public function updateSales(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        // فقط اگر در وضعیت درست هست
        if ($transport->status !== TransportStatus::RequestedBySales) {
            return back()->with('error', 'این مرحله دیگر قابل ویرایش نیست.');
        }

        $data = $request->validate([
            // بخش ۱
            'unloading_confirmed'   => ['required', Rule::in([0, 1])],
            'shipping_type'         => ['required', Rule::in(['inner_city', 'outer_city'])],
            'transfer_type'         => ['required', Rule::in(['single_stage', 'two_stage'])],
            'requested_truck_type'  => ['required', 'string', 'max:100'],
            'requested_wagon_type'  => ['nullable', 'string', 'max:100'],

            // بخش ۲
            'sender_name'           => ['required', 'string', 'max:255'],
            'sender_postal_code'    => ['required', 'string', 'max:20'],
            'sender_national_code'  => ['required', 'string', 'max:20'],
            'sender_phone'          => ['required', 'string', 'max:50'],

            // بخش ۳
            'receiver_company'          => ['required', 'string', 'max:255'],
            'receiver_name'             => ['required', 'string', 'max:255'],
            'receiver_postal_code'      => ['required', 'string', 'max:20'],
            'receiver_national_code'    => ['nullable', 'string', 'max:20'],
            'receiver_phone'            => ['nullable', 'string', 'max:50'],
            'receiver_mobile'           => ['required', 'string', 'max:50'],
            'receiver_activity_address' => ['required', 'string'],

            'unloading_place_approved'  => ['required', Rule::in([0, 1])],
            'unloading_address'         => ['nullable', 'string'],
            'unloading_postal_code'     => ['nullable', 'string', 'max:20'],
            'unloading_responsible'     => ['nullable', 'string', 'max:255'],
            'unloading_responsible_phone' => ['nullable', 'string', 'max:50'],

            // تیک تایید
            'approve_sales_expert'   => ['nullable', 'boolean'],
        ]);

        // پر کردن فیلدها
        $transport->fill($data);

        // اگر دو مرحله‌ای نیست، نوع واگن را خالی کن
        if ($data['transfer_type'] === 'single_stage') {
            $transport->requested_wagon_type = null;
        }

        // اگر محل تخلیه تایید شده، فیلدهای اضافی را خالی کن
        if ($data['unloading_place_approved'] == 1) {
            $transport->unloading_address          = null;
            $transport->unloading_postal_code      = null;
            $transport->unloading_responsible      = null;
            $transport->unloading_responsible_phone = null;
        }

        // اگر تیک تایید کارشناس فروش زده شده، وضعیت را جلو ببر
        if ($request->boolean('approve_sales_expert')) {
            $transport->status = TransportStatus::CompletedBySales;
        }

        $transport->save();

        return redirect()
            ->route('pre_invoices.transports.wizard.show', [$preInvoice->id, $transport->id])
            ->with('success', 'اطلاعات کارشناس فروش ثبت شد.');
    }

    /**
     * مرحله ۲: کارشناس خرید
     * (اطلاعات محل بارگیری، انتخاب محصول، ارزش/وزن، تیک تایید خرید)
     */
    public function updatePurchase(Request $request, PreInvoice $preInvoice, Transport $transport)
    {

        

        try {
            $data = $request->validate([
                'loadings'                           => 'required|array|min:1',
                'loadings.*.priority'                => 'required|integer|min:1',
                'loadings.*.buyer_name'              => 'nullable|string|max:255',
                'loadings.*.source_name'             => 'nullable|string|max:255',
                'loadings.*.phone'                   => 'required|string|max:50',
                'loadings.*.address'                 => 'required|string',
                'loadings.*.delivery_time'           => 'required|string',
                'loadings.*.voucher_row'             => 'nullable|string|max:50',

                'loadings.*.items'                   => 'nullable|array',
                'loadings.*.items.*.product_id'      => 'required|integer|exists:products,id',
                'loadings.*.items.*.quantity'        => 'required|numeric|min:0.001',
                'loadings.*.items.*.unit'            => 'nullable|string|max:50',
                'loadings.*.items.*.unit_price'      => 'required|numeric|min:0',
                'loadings.*.items.*.value_with_insurance' => 'required|numeric|min:0',

                'loadings.*.files'                   => 'nullable|array',
                'loadings.*.files.*.file'            => 'nullable|file|max:10240',
                'loadings.*.files.*.title'           => 'required_with:loadings.*.files.*.file|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        }
        // dd($data['loadings']);
        
        
        foreach ($data['loadings'] as $idx => &$row) {
            if (!empty($row['delivery_time'])) {
                try {
                    // ۱) تبدیل ارقام فارسی به انگلیسی
                    $normalized = \Morilog\Jalali\CalendarUtils::convertNumbers($row['delivery_time'], true);
                    // الان normalized چیزی مثل 1404-09-21 19:55 است

                    // ۲) تبدیل جلالی به Carbon میلادی
                    $g = Jalalian::fromFormat('Y-m-d H:i', $normalized)->toCarbon();

                    // ۳) ذخیره به فرمت مناسب برای DB
                    $row['delivery_time'] = $g->format('Y-m-d H:i:s');
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        "loadings.$idx.delivery_time" => ['فرمت تاریخ تحویل نامعتبر است.'],
                    ]);
                }
            }
        }
        unset($row);


        // $data = $request->validate([
        //     'loadings'                           => 'required|array|min:1',
        //     'loadings.*.priority'                => 'required|integer|min:1',
        //     'loadings.*.buyer_name'              => 'nullable|string|max:255',
        //     'loadings.*.source_name'             => 'nullable|string|max:255',
        //     'loadings.*.phone'                   => 'required|string|max:50',
        //     'loadings.*.address'                 => 'required|string',
        //     'loadings.*.delivery_time'           => 'required|date',
        //     'loadings.*.voucher_row'             => 'nullable|string|max:50',

        //     'loadings.*.items'                   => 'nullable|array',
        //     'loadings.*.items.*.product_id'      => 'required|integer|exists:products,id',
        //     'loadings.*.items.*.quantity'        => 'required|numeric|min:0.001',
        //     'loadings.*.items.*.unit'            => 'nullable|string|max:50',
        //     'loadings.*.items.*.unit_price'      => 'required|numeric|min:0',
        //     'loadings.*.items.*.value_with_insurance' => 'required|numeric|min:0',

        //     'loadings.*.files'                   => 'nullable|array',
        //     'loadings.*.files.*.file'            => 'nullable|file|max:10240',
        //     'loadings.*.files.*.title'           => 'required_with:loadings.*.files.*.file|string|max:255',
        // ]);

        // dd($validator->errors()->toArray());


        DB::transaction(function () use ($data, $transport) {
            // پاک‌کردن قبلی‌ها و ساخت جدید (یا می‌توانی diff بزنی)
            $transport->loadings()->each(function ($loading) {
                $loading->items()->delete();
                $loading->files()->delete();
                $loading->delete();
            });

            foreach ($data['loadings'] as $loadingIndex => $loadingRow) {

                // ۱) ایجاد رکورد loading
                $loading = $transport->loadings()->create([
                    'buyer_name'  => $loadingRow['buyer_name'] ?? null,
                    'source_name' => $loadingRow['source_name'] ?? null,
                    'phone'       => $loadingRow['phone'],
                    'address'     => $loadingRow['address'],
                    'priority'    => $loadingRow['priority'],
                    'delivery_time' => $loadingRow['delivery_time'],
                    'voucher_row'   => $loadingRow['voucher_row'] ?? null,
                ]);

                $totalValue  = 0;
                $totalWeight = 0;
                $totalQty    = 0;

                // ۲) محصولات این loading
                if (!empty($loadingRow['items'])) {

                    // enforce: همه محصولات این loading از یک منبع باشند
                    $sourceIds = [];
                    foreach ($loadingRow['items'] as $itemRow) {
                        $product = Product::find($itemRow['product_id']);
                        if ($product && $product->source_id) {
                            $sourceIds[$product->source_id] = true;
                        }
                    }
                    if (count($sourceIds) > 1) {
                        throw ValidationException::withMessages([
                            "loadings.$loadingIndex.items" =>
                                'در هر آدرس بارگیری فقط محصولات یک منبع می‌توانند انتخاب شوند.',
                        ]);
                    }

                    foreach ($loadingRow['items'] as $itemRow) {
                        $loading->items()->create([
                            'product_id'           => $itemRow['product_id'],
                            'quantity'             => $itemRow['quantity'],
                            'unit'                 => $itemRow['unit'] ?? null,
                            'unit_price'           => $itemRow['unit_price'],
                            'value_with_insurance' => $itemRow['value_with_insurance'],
                        ]);

                        $totalValue  += $itemRow['value_with_insurance'];
                        $totalWeight += $itemRow['quantity'];
                        $totalQty    += $itemRow['quantity'];
                    }
                }

                // ۳) فایل‌های این loading
                if (!empty($loadingRow['files'])) {
                    foreach ($loadingRow['files'] as $fileRow) {
                        if (!isset($fileRow['file'])) {
                            continue;
                        }
                        $storedPath = $fileRow['file']->store('transport/loadings', 'public');

                        $loading->files()->create([
                            'title' => $fileRow['title'],
                            'path'  => $storedPath,
                        ]);
                    }
                }

                // ۴) ثبت مجموع‌ها روی خود loading
                $loading->update([
                    'total_value_with_insurance' => $totalValue,
                    'total_weight'               => $totalWeight,
                    'total_quantity'             => $totalQty,
                ]);
            }

            // تنظیم وضعیت فرم حمل در گام خرید
            // $transport->update([
            //     'status'              => TransportStatus::COMPLETED_BY_PURCHASE,
            //     'approved_by_purchase'=> true,
            // ]);
            $transport->update([
                'status'              => TransportStatus::CompletedByPurchase,
                'approved_by_purchase'=> true,
            ]);

        });

        return back()->with('success', 'اطلاعات محل بارگیری با موفقیت ثبت شد.');
    }


    /**
     * مرحله ۳: مدیر لجستیک (انتخاب کارشناس حمل)
     */
    public function updateLogisticsManager(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        if ($transport->status !== TransportStatus::CompletedByPurchase) {
            return back()->with('error', 'این مرحله هنوز فعال نشده یا قابل ویرایش نیست.');
        }

        try {
           $data = $request->validate([
                'logistics_expert_id' => 'required|exists:users,id',
                'approve_logistics_manager'  => 'accepted',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        }

        $transport->update([
            'logistics_expert_id'       => $data['logistics_expert_id'],
            'status'                    => TransportStatus::AssignedToLogistics,
        ]);

        // dd($transport);

        return redirect()
            ->route('pre_invoices.transports.wizard.show', [$preInvoice->id, $transport->id])
            ->with('success', 'کارشناس مسئول حمل انتخاب شد.');
    }

    /**
     * مرحله ۴: کارشناس لجستیک (اطلاعات حمل، ماشین، واگن، هزینه‌ها، فایل‌ها)
     */
    public function updateLogisticsExpert(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        $data = $request->validate([
            'trucks'                                => 'required|array|min:1',
            'trucks.*.id'   => 'nullable|integer|exists:transport_vehicles,id',
            'trucks.*.is_wagon'                     => 'in:0',
            'trucks.*.vehicle_type'                 => 'required|string|max:255',
            'trucks.*.freight_company_name'         => 'required|string|max:255',
            'trucks.*.driver_name'                  => 'required|string|max:255',
            'trucks.*.driver_national_code'         => 'required|string|max:20',
            'trucks.*.driver_mobile'                => 'required|string|max:20',
            'trucks.*.driver_helper'                => 'nullable|string|max:255',
            'trucks.*.plate_iran'                   => 'nullable|string|max:4',
            'trucks.*.plate_3digit'                 => 'nullable|string|max:3',
            'trucks.*.plate_letter'                 => 'nullable|string|max:2',
            'trucks.*.plate_2digit'                 => 'nullable|string|max:2',
            'trucks.*.bill_of_lading_number'        => 'nullable|string|max:255',
            'trucks.*.planned_loading_at'           => 'required|string',
            'trucks.*.actual_loading_at'            => 'nullable|string',
            'trucks.*.arrival_at'                   => 'nullable|string',
            'trucks.*.unloading_at'                 => 'nullable|string',
            'trucks.*.total_freight_amount'         => 'nullable|integer|min:0',
            'trucks.*.loading_cost'                 => 'nullable|integer|min:0',
            'trucks.*.return_amount'                => 'nullable|integer|min:0',
            'trucks.*.description'                  => 'nullable|string',
            'trucks.*.status'                       => 'nullable|string|in:' . implode(',', TransportVehicleStatus::values()),

            'trucks.*.items'                        => 'nullable|array',
            'trucks.*.items.*.loading_id'           => 'required_with:trucks.*.items|exists:transport_loadings,id',
            'trucks.*.items.*.product_id'           => 'required_with:trucks.*.items|exists:products,id',
            'trucks.*.items.*.quantity'             => 'required_with:trucks.*.items|numeric|min:0.001',

            'trucks.*.files'                        => 'nullable|array',
            'trucks.*.files.*.title'                => 'nullable|string|max:255',
            'trucks.*.files.*.file'                 => 'nullable|file|max:10240',

            'trucks_existing_files_to_delete'       => 'nullable|array',
            'trucks_existing_files_to_delete.*'     => 'integer|exists:transport_vehicle_files,id',

            'wagons'                                => 'nullable|array',
            'wagons.*.id'   => 'nullable|integer|exists:transport_vehicles,id',
            'wagons.*.is_wagon'                     => 'in:1',
            'wagons.*.vehicle_type'                 => 'required_with:wagons|string|max:255',
            'wagons.*.freight_company_name'         => 'required_with:wagons|string|max:255',
            'wagons.*.planned_loading_at'           => 'required_with:wagons|string',
            'wagons.*.actual_loading_at'            => 'nullable|string',
            'wagons.*.arrival_at'                   => 'nullable|string',
            'wagons.*.unloading_at'                 => 'nullable|string',
            'wagons.*.wagon_cost'                   => 'nullable|integer|min:0',
            'wagons.*.wagon_coordinator_mobile'     => 'nullable|string|max:20',
            'wagons.*.wagon_contact_phone'          => 'nullable|string|max:20',
            'wagons.*.bill_of_lading_number'        => 'nullable|string|max:255',
            'wagons.*.description'                  => 'nullable|string',
            'wagons.*.status'                       => 'nullable|string|in:' . implode(',', TransportVehicleStatus::values()),

            'wagons.*.items'                        => 'nullable|array',
            'wagons.*.items.*.loading_id'           => 'required_with:wagons.*.items|exists:transport_loadings,id',
            'wagons.*.items.*.product_id'           => 'required_with:wagons.*.items|exists:products,id',
            'wagons.*.items.*.quantity'             => 'required_with:wagons.*.items|numeric|min:0.001',

            'wagons.*.files'                        => 'nullable|array',
            'wagons.*.files.*.title'                => 'nullable|string|max:255',
            'wagons.*.files.*.file'                 => 'nullable|file|max:10240',

            'wagons_existing_files_to_delete'       => 'nullable|array',
            'wagons_existing_files_to_delete.*'     => 'integer|exists:transport_vehicle_files,id',

            'approve_logistics_expert'              => 'accepted',
        ]);

        // تبدیل تاریخ جلالی → میلادی برای trucks و wagons
        foreach (['trucks', 'wagons'] as $group) {
            if (empty($data[$group])) {
                continue;
            }
            foreach ($data[$group] as $i => &$row) {
                foreach (['planned_loading_at', 'actual_loading_at', 'arrival_at', 'unloading_at'] as $field) {
                    if (!empty($row[$field])) {
                        $normalized  = CalendarUtils::convertNumbers($row[$field], true);
                        $carbon      = CalendarUtils::createCarbonFromFormat('Y-m-d H:i', $normalized);
                        $row[$field] = $carbon?->format('Y-m-d H:i:s');
                    } else {
                        $row[$field] = null;
                    }
                }
            }
            unset($row);
        }

        $trucksInput = $data['trucks'] ?? [];
        $wagonsInput = $data['wagons'] ?? [];

        $trucksFiles = $request->file('trucks', []);
        $wagonsFiles = $request->file('wagons', []);

        $deleteTruckFileIds = $data['trucks_existing_files_to_delete'] ?? [];
        $deleteWagonFileIds = $data['wagons_existing_files_to_delete'] ?? [];

        DB::transaction(function () use (
            $transport,
            $trucksInput,
            $wagonsInput,
            $trucksFiles,
            $wagonsFiles,
            $deleteTruckFileIds,
            $deleteWagonFileIds
        ) {
            $disk    = Storage::disk('public');
            $baseDir = 'logistics/transport-files';
            if (!$disk->exists($baseDir)) {
                $disk->makeDirectory($baseDir);
            }

            // حذف فایل‌های تیک‌خورده
            $allFileIdsToDelete = array_merge($deleteTruckFileIds, $deleteWagonFileIds);
            if (!empty($allFileIdsToDelete)) {
                $filesToDelete = TransportVehicleFile::whereIn('id', $allFileIdsToDelete)->get();
                foreach ($filesToDelete as $file) {
                    if ($file->path && $disk->exists($file->path)) {
                        $disk->delete($file->path);
                    }
                    $file->delete();
                }
            }

            $keptVehicleIds = [];

            // تابع کمکی برای ساخت payload مشترک
            $buildPayload = function (array $row, bool $isWagon) {
                $status = $row['status'] ?? TransportVehicleStatus::Searching->value;

                $payload = [
                    'is_wagon'              => $isWagon,
                    'freight_company_name'  => $row['freight_company_name'] ?? null,
                    'vehicle_type'          => $row['vehicle_type'] ?? null,
                    'status'                => $status,
                    'bill_of_lading_number' => $row['bill_of_lading_number'] ?? null,
                    'planned_loading_at'    => $row['planned_loading_at'] ?? null,
                    'actual_loading_at'     => $row['actual_loading_at'] ?? null,
                    'arrival_at'            => $row['arrival_at'] ?? null,
                    'unloading_at'          => $row['unloading_at'] ?? null,
                    'description'           => $row['description'] ?? null,
                ];

                if (!$isWagon) {
                    $payload = array_merge($payload, [
                        'driver_name'           => $row['driver_name'] ?? null,
                        'driver_national_code'  => $row['driver_national_code'] ?? null,
                        'driver_mobile'         => $row['driver_mobile'] ?? null,
                        'driver_helper'         => $row['driver_helper'] ?? null,
                        'plate_iran'            => $row['plate_iran'] ?? null,
                        'plate_3digit'          => $row['plate_3digit'] ?? null,
                        'plate_letter'          => $row['plate_letter'] ?? null,
                        'plate_2digit'          => $row['plate_2digit'] ?? null,
                        'total_freight_amount'  => $row['total_freight_amount'] ?? null,
                        'loading_cost'          => $row['loading_cost'] ?? null,
                        'return_amount'         => $row['return_amount'] ?? null,
                    ]);
                } else {
                    $payload = array_merge($payload, [
                        'wagon_cost'              => $row['wagon_cost'] ?? null,
                        'wagon_coordinator_mobile'=> $row['wagon_coordinator_mobile'] ?? null,
                        'wagon_contact_phone'     => $row['wagon_contact_phone'] ?? null,
                    ]);
                }

                return $payload;
            };

            // تابع برای ذخیره یا آپدیت وسیله
            $saveVehicle = function (array $row, bool $isWagon, int $index, array $filesArray, string $kind) use ($transport, $disk, $baseDir, &$keptVehicleIds, $buildPayload) 
            {
                $payload   = $buildPayload($row, $isWagon);
                $vehicleId = $row['id'] ?? null;

                if ($vehicleId) {
                    $vehicle = $transport->vehicles()->where('id', $vehicleId)->first();
                    if ($vehicle) {
                        

                        // مبلغ فعلی در دیتابیس
                        $oldAmount = $vehicle->is_wagon
                            ? $vehicle->wagon_cost
                            : $vehicle->total_freight_amount;

                        // مبلغ جدید از فرم
                        $newAmount = $vehicle->is_wagon
                            ? ($payload['wagon_cost'] ?? null)
                            : ($payload['total_freight_amount'] ?? null);

                             // اگر مبلغ تغییر کرده، وضعیت حسابداری را برگردان به pending
                        if ($oldAmount != $newAmount) {
                            $payload['freight_accounting_status'] = \App\Enums\FreightAccountingStatus::Pending->value;
                            $payload['freight_reject_reason']     = null;
                            $payload['freight_approved_at']       = null;
                            $payload['freight_approved_by']       = null;
                            $payload['freight_paid_at']           = null;
                            $payload['freight_paid_by']           = null;
                            $payload['freight_settled']           = false;
                        }

                        // --- آپدیت وسیله ---
                        $vehicle->update($payload);
                        // آیتم‌های قدیم را پاک می‌کنیم که دوباره از فرم بسازیم
                        $vehicle->items()->delete();

                    } else {
                        $vehicle = $transport->vehicles()->create($payload);
                    }
                } else {
                    $vehicle = $transport->vehicles()->create($payload);
                }

                $keptVehicleIds[] = $vehicle->id;

                // آیتم‌ها
                if (!empty($row['items'])) {
                    foreach ($row['items'] as $itemRow) {
                        $vehicle->items()->create([
                            'transport_loading_id' => $itemRow['loading_id'],
                            'product_id'           => $itemRow['product_id'],
                            'quantity'             => $itemRow['quantity'],
                        ]);
                    }
                }

                // فایل‌های جدید
                if (!empty($filesArray[$index]['files'])) {
                    foreach ($filesArray[$index]['files'] as $fIndex => $fileUpload) {
                        if (!$fileUpload || !isset($fileUpload['file']) || !$fileUpload['file']) {
                            continue;
                        }

                        /** @var \Illuminate\Http\UploadedFile $uploadedFile */
                        $uploadedFile = $fileUpload['file'];
                        $title        = $row['files'][$fIndex]['title'] ?? null;

                        $vehicleDir = $baseDir . '/' . $transport->id . '/' . $vehicle->id;
                        if (!$disk->exists($vehicleDir)) {
                            $disk->makeDirectory($vehicleDir);
                        }

                        $storedPath = $uploadedFile->store($vehicleDir, 'public');

                        $vehicle->files()->create([
                            'title'         => $title,
                            'path'          => $storedPath,
                            'original_name' => $uploadedFile->getClientOriginalName(),
                            'mime_type'     => $uploadedFile->getClientMimeType(),
                            'size'          => $uploadedFile->getSize(),
                            'kind'          => $kind,
                        ]);
                    }
                }

                return $vehicle;
            };

            // trucks
            foreach ($trucksInput as $tIndex => $truckRow) {
                $saveVehicle($truckRow, false, $tIndex, $trucksFiles, 'truck');
            }

            // wagons
            foreach ($wagonsInput as $wIndex => $wagonRow) {
                $saveVehicle($wagonRow, true, $wIndex, $wagonsFiles, 'wagon');
            }

            // حالا هر وسیله‌ای که id آن در فرم نیست را حذف کن
            if (!empty($keptVehicleIds)) {
                // $vehiclesToDelete = $transport->vehicles()
                //     ->whereNotIn('id', $keptVehicleIds)
                //     ->get();

                $vehiclesToDelete = $transport->vehicles()
                    ->whereNotIn('id', $keptVehicleIds)
                    ->whereIn('status', [
                        TransportVehicleStatus::Searching->value,
                        TransportVehicleStatus::Found->value,
                        TransportVehicleStatus::Loading->value,
                    ])
                    ->get();


                foreach ($vehiclesToDelete as $vehicle) {
                    foreach ($vehicle->files as $file) {
                        if ($file->path && $disk->exists($file->path)) {
                            $disk->delete($file->path);
                        }
                        $file->delete();
                    }
                    $vehicle->items()->delete();
                    $vehicle->delete();
                }
            }
            

            $transport->update([
                'status' => TransportStatus::InProgressByTransport->value,
            ]);
        });

        $preInvoice->updateStatusFromTransports();

        return back()->with('success', 'اطلاعات عملیات حمل به‌روزرسانی شد.');
    }


    public function approveVehicleFreight(Transport $transport, TransportVehicle $vehicle, Request $request)
    {
        $vehicle->update([
            'freight_accounting_status' => FreightAccountingStatus::Approved->value,
            'freight_reject_reason'     => null,
            'freight_approved_at'       => now(),
            'freight_approved_by'       => Auth::id(),
        ]);

        $this->maybeUpdateTransportStatusAfterAccounting($transport);

        if ($request->ajax()) {
            return response()->json([
                'status'         => 'ok',
                'freight_status' => 'approved',
                'reject_reason'  => null,
            ]);
        }

        return back()->with('success', 'هزینه این وسیله توسط حسابداری تأیید شد.');
    }


    public function rejectVehicleFreight(Request $request, Transport $transport, TransportVehicle $vehicle)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $vehicle->update([
            'freight_accounting_status' => FreightAccountingStatus::Rejected->value,
            'freight_reject_reason'     => $data['reason'],
            'freight_approved_at'       => null,
            'freight_approved_by'       => null,
            'freight_paid_at'           => null,
            'freight_paid_by'           => null,
            'freight_settled'           => false,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status'         => 'ok',
                'freight_status' => 'rejected',
                'reject_reason'  => $data['reason'],
            ]);
        }

        return back()->with('success', 'هزینه این وسیله توسط حسابداری رد شد.');
    }


    public function settleVehicleFreight(Request $request, Transport $transport, TransportVehicle $vehicle)
    {
        $data = $request->validate([
            'paid_at' => 'required|date',
        ]);

        $vehicle->update([
            'freight_paid_at' => $data['paid_at'],
            'freight_paid_by' => Auth::id(),
            'freight_settled' => true,
        ]);

        $this->maybeUpdateTransportStatusAfterAccounting($transport);

        if ($request->ajax()) {
            return response()->json([
                'status'         => 'ok',
                'freight_status' => $vehicle->freight_accounting_status->value,
                'reject_reason'  => $vehicle->freight_reject_reason,
            ]);
        }

        return back()->with('success', 'کرایه این وسیله تسویه شد.');
    }


    protected function maybeUpdateTransportStatusAfterAccounting(Transport $transport): void
    {
        $transport->load('vehicles');

        $allVehiclesOk = $transport->vehicles->every(function (TransportVehicle $v) {
            return $v->status === \App\Enums\TransportVehicleStatus::Unloaded
                && $v->freight_accounting_status === FreightAccountingStatus::Approved
                && $v->freight_settled;
        });

        if ($allVehiclesOk && $transport->status === TransportStatus::InProgressByTransport->value) {
            $transport->update([
                'status' => TransportStatus::CheckedByAccounting->value,
            ]);
        }
    }

    public function settleAllTransport(Transport $transport)
    {
        $transport->load('vehicles');

        $allVehiclesOk = $transport->vehicles->every(function (TransportVehicle $v) {
            return $v->status === \App\Enums\TransportVehicleStatus::Unloaded
                && $v->freight_accounting_status === FreightAccountingStatus::Approved
                && $v->freight_settled;
        });

        if (! $allVehiclesOk) {
            return back()->with('error', 'هنوز همه وسایل تخلیه و تسویه نشده‌اند.');
        }

        $transport->update([
            'status' => TransportStatus::CheckedByAccounting->value,
        ]);

        return back()->with('success', 'تسویه مالی این حمل انجام شد و سفارش به مرحله مدیر فروش رفت.');
    }




    /**
     * مرحله ۵: حسابداری
     */
    public function updateAccounting(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        if ($transport->status !== TransportStatus::LogisticsCompleted) {
            return back()->with('error', 'این مرحله هنوز فعال نشده یا قابل ویرایش نیست.');
        }

        $data = $request->validate([
            'accounting_approved'   => ['required', 'boolean'],
            'accounting_comment'    => ['nullable', 'string'],
            // فایل پرداخت هزینه راننده/واگن می‌تواند اینجا upload شود
        ]);

        $transport->accounting_approved = $data['accounting_approved'];
        $transport->accounting_comment  = $data['accounting_comment'] ?? null;

        if ($data['accounting_approved']) {
            $transport->status = TransportStatus::AccountingApproved;
        }

        $transport->save();

        // TODO: ذخیره فایل پرداخت هزینه راننده/واگن در صورت نیاز

        return redirect()
            ->route('pre_invoices.transports.wizard.show', [$preInvoice->id, $transport->id])
            ->with('success', 'نتیجه بررسی حسابداری ثبت شد.');
    }

    /**
     * مرحله ۶: مدیر فروش
     */
    public function updateSalesManager(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        if ($transport->status !== TransportStatus::AccountingApproved) {
            return back()->with('error', 'این مرحله هنوز فعال نشده یا قابل ویرایش نیست.');
        }

        $data = $request->validate([
            'sales_manager_approved' => ['required', 'boolean'],
            'sales_manager_comment'  => ['nullable', 'string'],
            'rollback_to_step'       => ['nullable', Rule::in([
                'sales', 'purchase', 'logistics_manager', 'logistics_expert', 'accounting',
            ])],
        ]);

        $transport->sales_manager_approved = $data['sales_manager_approved'];
        $transport->sales_manager_comment  = $data['sales_manager_comment'] ?? null;

        if ($data['sales_manager_approved']) {
            $transport->status = TransportStatus::SalesManagerApproved;
        } elseif (!empty($data['rollback_to_step'])) {
            // برگشت مرحله بر اساس انتخاب مدیر فروش
            $transport->status = match ($data['rollback_to_step']) {
                'sales'             => TransportStatus::RequestedBySales,
                'purchase'          => TransportStatus::CompletedBySales,
                'logistics_manager' => TransportStatus::CompletedByPurchase,
                'logistics_expert'  => TransportStatus::AssignedToLogisticsExpert,
                'accounting'        => TransportStatus::LogisticsCompleted,
                default             => $transport->status,
            };
        }

        $transport->save();

        return redirect()
            ->route('pre_invoices.transports.wizard.show', [$preInvoice->id, $transport->id])
            ->with('success', 'تصمیم مدیر فروش روی پرونده حمل ثبت شد.');
    }

    /**
     * مرحله ۷: نهایی‌سازی پرونده توسط مدیر لجستیک
     */
    public function updateLogisticsClose(Request $request, PreInvoice $preInvoice, Transport $transport)
    {
        if ($transport->status !== TransportStatus::SalesManagerApproved) {
            return back()->with('error', 'این مرحله هنوز فعال نشده یا قابل ویرایش نیست.');
        }

        $data = $request->validate([
            'closed'          => ['required', 'boolean'],
            'close_comment'   => ['nullable', 'string'],
        ]);

        if ($data['closed']) {
            $transport->status        = TransportStatus::Closed;
            $transport->close_comment = $data['close_comment'] ?? null;
        }

        $transport->save();

        return redirect()
            ->route('pre_invoices.transports.wizard.show', [$preInvoice->id, $transport->id])
            ->with('success', 'پرونده حمل نهایی شد.');
    }
}

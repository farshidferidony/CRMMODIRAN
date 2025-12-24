<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use App\Enums\TransportStatus;

use App\Enums\PreInvoiceStatus;
use App\Enums\TransportVehicleStatus;


class Transport extends Model
{
    use SoftDeletes, LogsActivity;
    // protected $fillable = [
    //     'invoice_id','product_ids','driver_id','truck_type','delivery_address_id','status','expenses'
    // ];

    protected $fillable = [
        'pre_invoice_id',
        'invoice_id',

        'status',
        'approved_by_sales_expert',
        'approved_by_purchase',
        'approved_by_transport_expert',
        'approved_by_accounting',
        'approved_by_sales_manager',

        'closed_by_logistics',
        'closed_at',

        'unloading_confirmed',
        'shipping_type',
        'transfer_type',
        'requested_truck_type',
        'requested_wagon_type',

        'sender_name',
        'sender_postal_code',
        'sender_national_code',
        'sender_phone',

        'receiver_company',
        'receiver_name',
        'receiver_postal_code',
        'receiver_national_code',
        'receiver_phone',
        'receiver_mobile',
        'receiver_activity_address',

        'unloading_place_approved',
        'unloading_address',
        'unloading_postal_code',
        'unloading_responsible',
        'unloading_responsible_phone',
    ];

    protected static $logAttributes =  [
        'pre_invoice_id',
        'invoice_id',

        'status',
        'approved_by_sales_expert',
        'approved_by_purchase',
        'approved_by_transport_expert',
        'approved_by_accounting',
        'approved_by_sales_manager',

        'closed_by_logistics',
        'closed_at',

        'unloading_confirmed',
        'shipping_type',
        'transfer_type',
        'requested_truck_type',
        'requested_wagon_type',

        'sender_name',
        'sender_postal_code',
        'sender_national_code',
        'sender_phone',

        'receiver_company',
        'receiver_name',
        'receiver_postal_code',
        'receiver_national_code',
        'receiver_phone',
        'receiver_mobile',
        'receiver_activity_address',

        'unloading_place_approved',
        'unloading_address',
        'unloading_postal_code',
        'unloading_responsible',
        'unloading_responsible_phone',
    ];
    
    protected static $logName = 'Transport';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    


    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function address() { return $this->belongsTo(Address::class, 'delivery_address_id'); }
    public function preInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'pre_invoice_id');
    }

    
    protected $casts = [
        'status' => TransportStatus::class,
        // اگر booleanها را هم بخواهی cast کنی:
        'approved_by_sales_expert'     => 'bool',
        'approved_by_purchase'         => 'bool',
        'approved_by_transport_expert' => 'bool',
        'approved_by_accounting'       => 'bool',
        'approved_by_sales_manager'    => 'bool',
        'approved_by_purchase' => 'bool',
    ];

    public function loadings()
    {
        return $this->hasMany(TransportLoading::class);
    }

    public function assignedLogisticsUser()
    {
        return $this->belongsTo(User::class, 'logistics_expert_id');
    }

    public function vehicles()
    {
        return $this->hasMany(TransportVehicle::class);
    }

      // آیا حداقل یک مبلغ وارد شده که حسابداری بتواند بررسی کند؟
    public function hasAnyFreightAmount(): bool
    {
        return $this->vehicles->contains(function ($v) {
            return ($v->is_wagon && $v->wagon_cost > 0)
                || (!$v->is_wagon && $v->total_freight_amount > 0);
        });
    }

    // گام حسابداری: بعد از تخلیه کامل و تأیید لجستیک
    public function canGoToAccounting(): bool
    {
        $allUnloaded = $this->vehicles->every(function ($v) {
            return $v->status === TransportVehicleStatus::Unloaded;
        });

        return $allUnloaded
            && $this->approved_by_transport_expert
            && $this->hasAnyFreightAmount(); // بدون مبلغ، حسابداری معنی ندارد
    }

    // گام مدیر فروش: همه‌ی وسایل تسویه شده و حسابداری تأیید کرده
    // public function canGoToSalesManager(): bool
    // {
    //     // ۱) همه‌ی آیتم‌ها از نظر مالی تسویه شده باشند
    //     $allSettled = $this->vehicles->every(function ($v) {
    //         return $v->freight_settled; // همان شرط مالی که قبلاً داشتی
    //     });

    //     // ۲) حداقل یک «ماشین» (not wagon) در مرحله بارگیری یا بالاتر باشد
    //     $hasAnyTruckLoadedOrMore = $this->vehicles->contains(function ($v) {
    //         if ($v->is_wagon) {
    //             return false;
    //         }

    //         return in_array($v->status, [
    //             TransportVehicleStatus::Loading,
    //             TransportVehicleStatus::Loaded,
    //             TransportVehicleStatus::EnRoute,
    //             TransportVehicleStatus::Arrived,
    //             TransportVehicleStatus::Unloading,
    //             TransportVehicleStatus::Unloaded,
    //         ]);
    //     });

    //     return $allSettled
    //         && $this->approved_by_accounting
    //         && $hasAnyTruckLoadedOrMore;
    // }

    // حداقل یک "ماشین" (is_wagon = 0) با وضعیت بارگیری یا بالاتر
    public function hasAnyTruckLoadedOrMore(): bool
    {
        return $this->vehicles->contains(function ($v) {
            if ($v->is_wagon) {
                return false; // واگن‌ها برای مدیر فروش مهم نیستند
            }

            return in_array($v->status, [
                TransportVehicleStatus::Loading,
                TransportVehicleStatus::Loaded,
                TransportVehicleStatus::EnRoute,
                TransportVehicleStatus::Arrived,
                TransportVehicleStatus::Unloading,
                TransportVehicleStatus::Unloaded,
            ]);
        });
    }

    // آیا Step مدیر فروش در ویزارد نمایش/فعال شود؟
    public function canShowSalesManagerStep(): bool
    {
        // فقط شرط فیزیکی: حداقل یک ماشین در بارگیری یا بالاتر
        return $this->hasAnyTruckLoadedOrMore();
    }

    // آیا مدیر فروش اجازه دارد تأیید نهایی این فرم حمل را ثبت کند؟
    public function canApproveBySalesManager(): bool
    {
        // اگر می‌خواهی بدون تسویه مالی هم اجازه تایید بدهد:
        return $this->hasAnyTruckLoadedOrMore();

        // اگر بعداً خواستی وابسته به مالی شود، می‌توانی این‌طورش کنی:
        /*
        $allSettled = $this->vehicles->every(function ($v) {
            return $v->freight_settled;
        });

        return $this->hasAnyTruckLoadedOrMore() && $allSettled && $this->approved_by_accounting;
        */
    }



    // بستن پرونده توسط لجستیک
    public function canCloseByLogistics(): bool
    {
        $allUnloaded = $this->vehicles->every(function ($v) {
            return $v->status === TransportVehicleStatus::Unloaded;
        });

        $allSettled = $this->vehicles->every(function ($v) {
            return $v->freight_settled;
        });

        return $allUnloaded
            && $allSettled
            && $this->approved_by_accounting
            && $this->approved_by_sales_manager;
    }
    

}

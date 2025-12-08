<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Transport extends Model
{
    use SoftDeletes, LogsActivity;
    // protected $fillable = [
    //     'invoice_id','product_ids','driver_id','truck_type','delivery_address_id','status','expenses'
    // ];

    protected $fillable = [
        // ارتباط‌ها
        'pre_invoice_id',
        'invoice_id',
        'driver_id',
        'carrier_company_id',

        // وضعیت‌های فرآیند فرم حمل
        'status',                      // requested_by_sales, completed_by_sales, completed_by_purchase, ...
        'approved_by_sales_expert',
        'approved_by_purchase',
        'approved_by_transport_expert',
        'approved_by_accounting',
        'approved_by_sales_manager',

        // تنظیمات حمل (گام کارشناس فروش)
        'unloading_confirmed',         // تایید تخلیه: دارد/ندارد
        'shipping_type',               // inner_city, outer_city
        'transfer_type',               // single_stage, two_stage
        'requested_truck_type',        // نوع ماشین درخواستی
        'requested_wagon_type',        // نوع واگن (در حمل دو مرحله‌ای)

        // اطلاعات فرستنده
        'sender_name',
        'sender_postal_code',
        'sender_national_code',
        'sender_phone',

        // اطلاعات گیرنده و محل فعالیت
        'receiver_company',
        'receiver_name',
        'receiver_postal_code',
        'receiver_national_code',
        'receiver_phone',
        'receiver_mobile',
        'receiver_activity_address',

        // محل تخلیه
        'unloading_place_approved',
        'unloading_address',
        'unloading_postal_code',
        'unloading_responsible',
        'unloading_responsible_phone',

        // اطلاعات عملیاتی حمل (گام کارشناس حمل)
        'truck_type',                  // نوع ماشین واقعی حمل‌کننده
        'shipping_date',
        'total_freight',
        'loading_cost',
        'waybill_number',
        'return_cost',

        // پلاک ماشین
        'plate_iran',
        'plate_3digits',
        'plate_literal',
        'plate_2digits',

        // فایل‌ها / ضمیمه‌ها
        'carrier_attachment_path',

        // فیلدهای کمکی دیگر در صورت نیاز...
    ];

    protected static $logAttributes =  [
        // ارتباط‌ها
        'pre_invoice_id',
        'invoice_id',
        'driver_id',
        'carrier_company_id',

        // وضعیت‌های فرآیند فرم حمل
        'status',                      // requested_by_sales, completed_by_sales, completed_by_purchase, ...
        'approved_by_sales_expert',
        'approved_by_purchase',
        'approved_by_transport_expert',
        'approved_by_accounting',
        'approved_by_sales_manager',

        // تنظیمات حمل (گام کارشناس فروش)
        'unloading_confirmed',         // تایید تخلیه: دارد/ندارد
        'shipping_type',               // inner_city, outer_city
        'transfer_type',               // single_stage, two_stage
        'requested_truck_type',        // نوع ماشین درخواستی
        'requested_wagon_type',        // نوع واگن (در حمل دو مرحله‌ای)

        // اطلاعات فرستنده
        'sender_name',
        'sender_postal_code',
        'sender_national_code',
        'sender_phone',

        // اطلاعات گیرنده و محل فعالیت
        'receiver_company',
        'receiver_name',
        'receiver_postal_code',
        'receiver_national_code',
        'receiver_phone',
        'receiver_mobile',
        'receiver_activity_address',

        // محل تخلیه
        'unloading_place_approved',
        'unloading_address',
        'unloading_postal_code',
        'unloading_responsible',
        'unloading_responsible_phone',

        // اطلاعات عملیاتی حمل (گام کارشناس حمل)
        'truck_type',                  // نوع ماشین واقعی حمل‌کننده
        'shipping_date',
        'total_freight',
        'loading_cost',
        'waybill_number',
        'return_cost',

        // پلاک ماشین
        'plate_iran',
        'plate_3digits',
        'plate_literal',
        'plate_2digits',

        // فایل‌ها / ضمیمه‌ها
        'carrier_attachment_path',

        // فیلدهای کمکی دیگر در صورت نیاز...
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
}

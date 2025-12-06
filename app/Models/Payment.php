<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use  LogsActivity;
    // protected $fillable = [
    //     'invoice_id','pre_invoice_id','customer_id','amount','payment_type','paid_date','status'
    // ];
    protected $fillable = [
        'invoice_id','pre_invoice_id','customer_id',
        'amount','paid_amount','payment_type',
        'paid_date','status','scheduled_date',
        'actual_paid_date','receipt_path','plan_id',
        'finance_reject_reason',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function preInvoice() { return $this->belongsTo(PreInvoice::class); }
    public function customer() { return $this->belongsTo(Customer::class); }

    protected static $logAttributes = [
        'invoice_id','pre_invoice_id','customer_id',
        'amount','paid_amount','payment_type',
        'paid_date','status','scheduled_date',
        'actual_paid_date','receipt_path','plan_id',
        'finance_reject_reason',
    ];

    protected static $logName = 'Payment';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

        
    public function plan()
    {
        return $this->belongsTo(InvoicePaymentPlan::class,'plan_id');
    }

}

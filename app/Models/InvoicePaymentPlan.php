<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class InvoicePaymentPlan extends Model
{
    protected $fillable = ['invoice_id','amount','payment_type','scheduled_date','is_completed','note'];

    protected static $logAttributes = ['invoice_id','amount','payment_type','scheduled_date','is_completed','note'];
    protected static $logName = 'InvoicePaymentPlan';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class,'plan_id');
    }

    public function scopeDueBetween($query, $from, $to)
    {
        return $query->whereBetween('scheduled_date', [$from, $to])
                    ->where('is_completed', false)
                    ->with('invoice.customer');
    }

    public function preInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'pre_invoice_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Models\Customer;
use App\Models\InvoiceItem;




class Invoice extends Model
{
    use  LogsActivity;
    protected $fillable = ['customer_id','type','status','total_amount','formal_extra','created_by'];
    // public function items() { return $this->hasMany(InvoiceItem::class); }

    protected static $logAttributes = ['customer_id','type','status','total_amount','formal_extra','created_by'];
    protected static $logName = 'Invoice';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

     public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }

    // Invoice.php
    // public function getRemainingAmountAttribute()
    // {
    //     $paid = $this->payments()
    //         ->where('status','confirmed')
    //         ->sum(DB::raw('COALESCE(paid_amount, amount)'));

    //     return max(0, $this->total_amount - $paid);
    // }


     public function plans()
    {
        return $this->hasMany(InvoicePaymentPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()
            ->where('status','confirmed')
            ->sum(DB::raw('COALESCE(paid_amount, amount)'));
    }

    // public function getRemainingAmountAttribute()
    // {
    //     return max(0, $this->total_amount - $this->paid_amount);
    // }

    // Invoice.php


    public function getRemainingAmountAttribute()
    {
        $paid = $this->paid_sum ?? $this->payments()
            ->where('status','confirmed')
            ->sum('paid_amount');
        return max(0, $this->total_amount - $paid);
    }


    public function scopeWithBalance(Builder $query)
    {
        return $query->withSum(['payments as paid_sum' => function ($q) {
            $q->where('status','confirmed');
        }], 'paid_amount');
    }

    public function scopeDebtors(Builder $query)
    {
        // اینجا HAVING نمی‌زنیم، فقط paid_sum را می‌آوریم
        return $query->withBalance();
    }

     public function transports()
    {
        return $this->hasMany(Transport::class);
    }

    public function paymentPlans()
    {
        return $this->hasMany(InvoicePaymentPlan::class);
    }

}

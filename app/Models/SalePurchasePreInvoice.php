<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class SalePurchasePreInvoice extends Model
{
    protected $fillable = [
        'sale_pre_invoice_id',
        'purchase_pre_invoice_id',
        'source_id',
        'created_by',
        'status',
    ];

    protected static $logAttributes = ['sale_pre_invoice_id',
        'purchase_pre_invoice_id',
        'source_id',
        'created_by',
        'status'
        ];
    protected static $logName = 'PreInvoice';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function salePreInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'sale_pre_invoice_id');
    }

    public function purchasePreInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'purchase_pre_invoice_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseAssignment extends Model
{
    protected $fillable = [
        'pre_invoice_item_id',
        'buyer_id',
        'source_id',
        'status',
        'unit_price',
        'note',
        'created_by',
    ];

    protected static $logAttributes = [
        'pre_invoice_item_id',
        'buyer_id',
        'source_id',
        'status',
        'unit_price',
        'note',
        'created_by',
    ];
    protected static $logName = 'PurchaseAssignment';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function item()
    {
        return $this->belongsTo(PreInvoiceItem::class, 'pre_invoice_item_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
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

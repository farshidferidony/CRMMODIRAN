<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PreInvoiceItem extends Model
{
    use  LogsActivity;
    // protected $fillable = ['pre_invoice_id','product_id','quantity','unit_price','attributes','total'];
    protected $fillable = [
        'pre_invoice_id',
        'purchase_pre_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'attributes',
        'total',
        'chosen_purchase_assignment_id',
        'sale_unit_price',
        'purchase_unit_price',
        'profit_percent',
        'final_purchase_weight',
        'purchase_status',
    ];
    // public function preInvoice() { return $this->belongsTo(PreInvoice::class); }
    public function preInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'pre_invoice_id');
    }
    public function product() { return $this->belongsTo(Product::class); }

    protected static $logAttributes = [
        'pre_invoice_id',
        'purchase_pre_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'attributes',
        'total',
        'chosen_purchase_assignment_id',
        'sale_unit_price',
        'purchase_unit_price',
        'profit_percent',
        'final_purchase_weight',
        'purchase_status',
    ];
    protected static $logName = 'PreInvoiceItem';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    protected $casts = [
        'final_quantity' => 'float',
        'final_unit_price' => 'float',
        'final_total_price' => 'float',
    ];

    public function purchaseAssignments()
    {
        return $this->hasMany(PurchaseAssignment::class, 'pre_invoice_item_id');
    }

    public function chosenPurchaseAssignment()
    {
        return $this->belongsTo(PurchaseAssignment::class, 'chosen_purchase_assignment_id');
    }

    public function salePreInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'pre_invoice_id');
    }

    public function purchasePreInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'purchase_pre_invoice_id');
    }

    public function assignments()
    {
        return $this->hasMany(PurchaseAssignment::class);
    }

    public function chosenAssignment()
    {
        return $this->belongsTo(PurchaseAssignment::class, 'chosen_purchase_assignment_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class); // اگر مدل منبع داری
    }

}



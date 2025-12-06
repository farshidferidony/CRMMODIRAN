<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Transport extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = [
        'invoice_id','product_ids','driver_id','truck_type','delivery_address_id',
        'status','expenses'
    ];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function address() { return $this->belongsTo(Address::class, 'delivery_address_id'); }

    protected static $logAttributes = ['invoice_id','product_ids','driver_id','truck_type','delivery_address_id',
        'status','expenses'];
    protected static $logName = 'Transport';
}

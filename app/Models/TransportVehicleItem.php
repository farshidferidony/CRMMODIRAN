<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class TransportVehicleItem extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'transport_vehicle_id',
        'transport_loading_id',
        'product_id',
        'quantity',
    ];

    protected static $logAttributes = [
        'transport_vehicle_id',
        'transport_loading_id',
        'product_id',
        'quantity',
    ];

    protected static $logName = 'TransportVehicleItem';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'transport_vehicle_id');
    }

    public function loading(): BelongsTo
    {
        return $this->belongsTo(TransportLoading::class, 'transport_loading_id'); // نام مدل loading تو را این‌جا بگذار
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

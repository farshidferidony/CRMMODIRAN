<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TransportVehicleFile extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'transport_vehicle_id',
        'title',
        'path',
    ];

    protected static $logAttributes = [
        'transport_vehicle_id',
        'title',
        'path',
    ];

    protected static $logName = 'TransportVehicleFile';

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
}

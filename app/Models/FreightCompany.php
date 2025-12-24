<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FreightCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'national_id',
        'phone',
        'mobile',
        'address',
    ];

    protected static $logAttributes = [
        'customer_scope',
        'source',
        'is_active',
        'creator_id',
    ];

    protected static $logName = 'FreightCompany';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(TransportVehicle::class);
    }
}

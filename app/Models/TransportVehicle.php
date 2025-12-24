<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\TransportVehicleStatus;
use App\Enums\FreightAccountingStatus;



class TransportVehicle extends Model
{

    use SoftDeletes;
    
    protected $fillable = [
        'transport_id',
        'is_wagon',
        'freight_company_id',
        'freight_company_name',
        'vehicle_type',
        'status',
        'driver_name',
        'driver_national_code',
        'driver_mobile',
        'driver_helper',
        'plate_iran',
        'plate_3digit',
        'plate_letter',
        'plate_2digit',
        'bill_of_lading_number',
        'planned_loading_at',
        'actual_loading_at',
        'arrival_at',
        'unloading_at',
        'total_freight_amount',
        'loading_cost',
        'return_amount',
        'wagon_cost',
        'wagon_coordinator_mobile',
        'wagon_contact_phone',
        'description',
        'freight_accounting_status',
        'freight_reject_reason',
        'freight_approved_at',
        'freight_approved_by',
        'freight_paid_at',
        'freight_paid_by',
        'freight_settled',
    ];

    protected static $logAttributes = [
        'transport_id',
        'is_wagon',
        'freight_company_id',
        'freight_company_name',
        'vehicle_type',
        'status',
        'driver_name',
        'driver_national_code',
        'driver_mobile',
        'driver_helper',
        'plate_iran',
        'plate_3digit',
        'plate_letter',
        'plate_2digit',
        'bill_of_lading_number',
        'planned_loading_at',
        'actual_loading_at',
        'arrival_at',
        'unloading_at',
        'total_freight_amount',
        'loading_cost',
        'return_amount',
        'wagon_cost',
        'wagon_coordinator_mobile',
        'wagon_contact_phone',
        'description',
        'freight_accounting_status',
        'freight_reject_reason',
        'freight_approved_at',
        'freight_approved_by',
        'freight_paid_at',
        'freight_paid_by',
        'freight_settled',
    ];

    protected static $logName = 'TransportVehicle';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    protected $casts = [
        'is_wagon'            => 'boolean',
        'planned_loading_at'  => 'datetime',
        'actual_loading_at'   => 'datetime',
        'arrival_at'          => 'datetime',
        'unloading_at'        => 'datetime',
        'status'              => TransportVehicleStatus::class,
        'freight_accounting_status' => FreightAccountingStatus::class,
        'freight_settled'     => 'boolean',
    ];


    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }

    public function freightCompany(): BelongsTo
    {
        return $this->belongsTo(FreightCompany::class);
    }

    // app/Models/TransportVehicle.php
    public function items()
    {
        return $this->hasMany(TransportVehicleItem::class, 'transport_vehicle_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TransportVehicleFile::class);
    }
}

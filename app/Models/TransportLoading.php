<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TransportLoading extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'transport_id',
        'buyer_name',
        'source_name',
        'phone',
        'address',
        'priority',
        'delivery_time',
        'voucher_row',
        'total_value_with_insurance',
        'total_weight',
        'total_quantity',
    ];

    protected $casts = [
        'delivery_time' => 'datetime',
    ];

    protected static $logAttributes = [
        'transport_id',
        'buyer_name',
        'source_name',
        'phone',
        'address',
        'priority',
        'delivery_time',
        'voucher_row',
        'total_value_with_insurance',
        'total_weight',
        'total_quantity',
    ];
    
    protected static $logName = 'TransportLoading';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    public function items()
    {
        return $this->hasMany(TransportLoadingItem::class);
    }

    public function files()
    {
        return $this->hasMany(TransportLoadingFile::class);
    }
}

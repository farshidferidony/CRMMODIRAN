<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contact extends Model
{
    use  LogsActivity, SoftDeletes;  
    protected $fillable = [
        'address_id',
        'type',
        'value',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function customer() { return $this->belongsTo(Customer::class); }



    protected static $logAttributes = [
        'address_id',
        'type',
        'value',
    ];
    protected static $logName = 'Contact';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function contactable()
    {
        return $this->morphTo();
    }


}

    
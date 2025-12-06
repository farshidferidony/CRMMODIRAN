<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Company extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = [
        'name', 'registration_number', 'economic_code', 'address_id'
    ];
    // روابط: addresses, customers

    protected static $logAttributes = ['name', 'registration_number', 'economic_code', 'address_id'];
    protected static $logName = 'Company';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'company_customer')
                    ->withPivot('position')
                    ->withTimestamps();
    }

    public function addresses() {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function people() {
        return $this->hasMany(Customer::class, 'company_id'); // یا اگر جدول واسط دارید belongsToMany
    }



}

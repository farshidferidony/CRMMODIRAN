<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;
    
    protected $fillable = [
        'type', 'first_name', 'last_name', 'passport_number', 'national_code', 'company_id', 'birthdate', 'email'
    ];
    // روابط: company, contacts, addresses


    protected static $logAttributes = ['type', 'first_name', 'last_name', 'passport_number', 'national_code', 'company_id',
        'birthdate', 'email'];
    protected static $logName = 'Customer';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_customer')
                    ->withPivot('position')
                    ->withTimestamps();
    }

    // public function companies()
    // {
    //     return $this->belongsToMany(Company::class, 'company_customer');
    // }


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function addresses() {
        return $this->morphMany(Address::class, 'addressable');
    }


}

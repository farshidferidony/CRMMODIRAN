<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Person extends Model
{
    use SoftDeletes;

     protected $table = 'persons';

    protected $fillable = [
        'first_name',
        'last_name',
        'national_code',
        'passport_number',
        'birthdate',
        'email',
    ];

    protected static $logAttributes = [
        'first_name',
        'last_name',
        'national_code',
        'passport_number',
        'birthdate',
        'email',
    ];
    
    protected static $logName = 'Person';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function customers()
    {
        return $this->morphToMany(Customer::class, 'linkable', 'customer_links');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_customer_roles')
            ->withPivot('role')
            ->withTimestamps();
    }

    // public function contacts()
    // {
    //     return $this->morphMany(Contact::class, 'contactable');
    // }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

}

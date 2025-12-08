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
    use SoftDeletes;

    protected $fillable = [
        'source',
        'is_active',
        'creator_id',
    ];

    protected static $logAttributes = [
        'source',
        'is_active',
        'creator_id',
    ];

    protected static $logName = 'Customer';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function links()
    {
        return $this->hasMany(CustomerLink::class);
    }

    public function persons()
    {
        return $this->morphedByMany(Person::class, 'linkable', 'customer_links');
    }

    public function companies()
    {
        return $this->morphedByMany(Company::class, 'linkable', 'customer_links');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    
    /**
    * شخص اصلی (اولین شخص لینک‌شده به این مشتری)
    */
    public function primaryPerson()
    {
        return $this->persons()->first();
    }

    /**
    * شرکت اصلی (اولین شرکت لینک‌شده به این مشتری)
    */
    public function primaryCompany()
    {
        return $this->companies()->first();
    }

    public function getDisplayNameAttribute()
    {
        if ($this->company) {
            return $this->company->name;
        }

        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Support\Str;

// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

// class Customer extends Model
// {
//     use SoftDeletes, LogsActivity;
    
//     protected $fillable = [
//         'type', 'first_name', 'last_name', 'passport_number', 'national_code', 'company_id', 'birthdate', 'email'
//     ];


//     protected static $logAttributes = ['type', 'first_name', 'last_name', 'passport_number', 'national_code', 'company_id',
//         'birthdate', 'email'];
//     protected static $logName = 'Customer';

//     public function getActivitylogOptions(): LogOptions
//     {
//         return LogOptions::defaults()
//             ->logFillable()
//             ->useLogName('User');
//     }

//     public function companies()
//     {
//         return $this->belongsToMany(Company::class, 'company_customer_roles')
//             ->withPivot(['role'])
//             ->withTimestamps();
//     }

//       // اگر هنوز ستون company_id روی customers داری و نمی‌خواهی همه‌جا را بشکنی:
//     public function mainCompany()
//     {
//         return $this->belongsTo(Company::class, 'company_id');
//     }

//     public function isPerson(): bool
//     {
//         return $this->type === 'person';
//     }

//     public function isCompany(): bool
//     {
//         return $this->type === 'company';
   

//     public function company()
//     {
//         return $this->belongsTo(Company::class, 'company_id');
//     }


//     public function addresses() {
//         return $this->morphMany(Address::class, 'addressable');
//     }


// }

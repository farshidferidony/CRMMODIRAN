<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Address extends Model
{
    use  LogsActivity;
    
    protected $fillable = ['country_id', 'province_id', 'city_id', 'postal_code', 'address_detail', 'floor', 'unit'];
    public function addressable() { return $this->morphTo(); }
    public function contacts() { return $this->hasMany(Contact::class); }


    protected static $logAttributes = ['country_id', 'province_id', 'city_id', 'postal_code', 'address_detail', 'floor', 'unit'];
    protected static $logName = 'Address';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function country()
    {
        return $this->belongsTo(CountryFaEn::class, 'country_id');
    }

    public function province()
    {
        return $this->belongsTo(ProvinceFaEn::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(CityFaEn::class, 'city_id');
    }


}

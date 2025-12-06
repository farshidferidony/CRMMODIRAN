<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceFaEn extends Model
{
    protected $table = 'province_fa_en';

    public function country()
    {
        return $this->belongsTo(CountryFaEn::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(CityFaEn::class, 'province_id');
    }
}
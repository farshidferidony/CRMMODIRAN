<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityFaEn extends Model
{
    protected $table = 'city_fa_en';

    public function province()
    {
        return $this->belongsTo(ProvinceFaEn::class, 'province_id');
    }
}

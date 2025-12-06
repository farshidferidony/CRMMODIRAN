<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryFaEn extends Model
{
    protected $table = 'country_fa_en';
    // relations: provinces
    public function provinces()
    {
        return $this->hasMany(ProvinceFaEn::class, 'country_id');
    }
}
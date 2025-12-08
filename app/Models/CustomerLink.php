<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLink extends Model
{
    protected $fillable = [
        'customer_id',
        'linkable_type',
        'linkable_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function linkable()
    {
        return $this->morphTo();
    }
}

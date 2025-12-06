<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Fund extends Model
{
    use  LogsActivity;
    protected $fillable = ['customer_id','balance','last_update'];
    public function customer() { return $this->belongsTo(Customer::class); }

    protected static $logAttributes = ['customer_id','balance','last_update'];
    protected static $logName = 'Fund';
}

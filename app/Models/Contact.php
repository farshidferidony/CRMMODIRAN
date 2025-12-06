<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contact extends Model
{
    use  LogsActivity;  
    protected $fillable = ['address_id', 'type', 'value'];
    public function customer() { return $this->belongsTo(Customer::class); }

    public function address() { return $this->belongsTo(Address::class); }


    protected static $logAttributes = ['address_id', 'type', 'value'];
    protected static $logName = 'Contact';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }


}

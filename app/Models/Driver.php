<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Driver extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = ['name','license_number','car_plate','phone'];
    public function transports() { return $this->hasMany(Transport::class); }

    protected static $logAttributes = ['name','license_number','car_plate','phone'];
    protected static $logName = 'Driver';
}

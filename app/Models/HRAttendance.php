<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class HRAttendance extends Model
{
    use  LogsActivity;
    protected $fillable = ['user_id','date','status','check_in','check_out','note'];
    public function user() { return $this->belongsTo(User::class); }

    protected static $logAttributes = ['user_id','date','status','check_in','check_out','note'];
    protected static $logName = 'HRAttendance';
}

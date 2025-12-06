<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class HRLeave extends Model
{
    use  LogsActivity;
    protected $fillable = ['user_id','from','to','approval_status','reason','approved_by'];
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    protected static $logAttributes = ['user_id','from','to','approval_status','reason','approved_by'];
    protected static $logName = 'HRLeave';
}

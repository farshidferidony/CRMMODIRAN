<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Notification extends Model
{
    use  LogsActivity;
    protected $fillable = ['user_id','type','title','message','status','scheduled_at'];
    public function user() { return $this->belongsTo(User::class); }

    protected static $logAttributes = ['user_id','type','title','message','status','scheduled_at'];
    protected static $logName = 'Notification';
}

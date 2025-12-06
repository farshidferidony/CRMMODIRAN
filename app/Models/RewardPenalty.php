<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class RewardPenalty extends Model
{
    use  LogsActivity;
    protected $fillable = ['user_id','type','source','invoice_id','amount','reason'];
    public function user() { return $this->belongsTo(User::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }

    protected static $logAttributes = ['user_id','type','source','invoice_id','amount','reason'];
    protected static $logName = 'RewardPenalty';
}

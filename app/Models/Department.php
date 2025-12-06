<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Department extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = ['name','manager_id'];
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function users() { return $this->hasMany(User::class); }

    protected static $logAttributes = ['name','manager_id'];
    protected static $logName = 'Department';
}

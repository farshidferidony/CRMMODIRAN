<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class TransportLoadingFile extends Model
{
    protected $fillable = ['transport_loading_id', 'title', 'path'];

    
    protected static $logAttributes = ['transport_loading_id', 'title', 'path'];
    
    protected static $logName = 'TransportLoadingFile';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function loading()
    {
        return $this->belongsTo(TransportLoading::class, 'transport_loading_id');
    }
}

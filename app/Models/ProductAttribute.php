<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductAttribute extends Model
{
    use  LogsActivity;
    protected $fillable = ['category_id','name','type','values'];
    public function category() { return $this->belongsTo(ProductCategory::class, 'category_id'); }

    protected static $logAttributes = ['category_id','name','type','values'];
    protected static $logName = 'ProductAttribute';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

}

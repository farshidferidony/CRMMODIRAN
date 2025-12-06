<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Product extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = ['name','category_id','description','price','stock'];
    public function category() { return $this->belongsTo(ProductCategory::class); }
    public function attributeValues() { return $this->hasMany(ProductAttributeValue::class); }

    protected static $logAttributes = ['name','category_id','description','price','stock'];
    protected static $logName = 'Product';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

}

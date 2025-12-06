<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductAttributeValue extends Model
{
    use  LogsActivity;
    protected $fillable = ['product_id','attribute_id','value'];
    public function product() { return $this->belongsTo(Product::class); }
    public function attribute() { return $this->belongsTo(ProductAttribute::class, 'attribute_id'); }

    protected static $logAttributes = ['product_id','attribute_id','value'];
    protected static $logName = 'ProductAttributeValue';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

}

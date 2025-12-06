<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductCategory extends Model
{
    use  LogsActivity;
    protected $fillable = ['name', 'parent_id', 'description'];
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    // public function attributes() { return $this->hasMany(ProductAttribute::class); }
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'category_id');
        // نه product_category_id
    }


    protected static $logAttributes = ['name', 'parent_id', 'description'];
    protected static $logName = 'ProductCategory';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    // app/Models/ProductCategory.php

    public function inheritedAttributes()
    {
        $category = $this;
        $all = collect();

        // از پایین به بالا می‌رویم تا ریشه
        while ($category) {
            $all = $category->attributes()->select('id','name','type','values','category_id')
                ->get()
                ->merge($all); // والدین قبلی پایین‌تر می‌مانند
            $category = $category->parent;
        }

        // اگر نخواستی duplicate بر اساس name داشته باشی، می‌توانی بر اساس name یکتا کنی:
        return $all->unique('name')->values();
    }

    public function supervisors()
    {
        return $this->belongsToMany(
            User::class,
            'category_role_user',
            'product_category_id',
            'user_id'
        )->withPivot('role_id')->withTimestamps();
    }


}

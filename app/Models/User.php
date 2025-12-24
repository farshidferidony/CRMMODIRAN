<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static $logAttributes = ['name',
        'email',
        'password',
        'remember_token'];
    protected static $logName = 'User';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

     public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // public function hasRole(string|array $roles): bool
    // {
    //     $roles = (array) $roles;
    //     return $this->roles()->whereIn('name', $roles)->exists();
    // }

    public function hasRole(string|array $roles): bool
    {
        // اگر نقش‌ها را به‌صورت چند آرایه یا چند پارامتر داده باشند، صافش کن
        $roles = is_array($roles) ? $roles : func_get_args();

        // تبدیل همه مقادیر به رشته و صاف کردن آرایه‌های تو‌در‌تو
        $roles = collect($roles)
            ->flatten()
            ->filter()
            ->map(fn ($r) => (string) $r)
            ->unique()
            ->values()
            ->all();

        return $this->roles()->whereIn('name', $roles)->exists();
    }


    public function categoryRoles()
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'category_role_user',
            'user_id',
            'product_category_id'
        )->withPivot('role_id')->withTimestamps();
    }

    public function supervisedCategories()
    {
        // فقط دسته‌هایی که نقش سرپرستی دارند؛ اگر بعداً خواستی فیلتر کنی
        return $this->categoryRoles()->wherePivotIn('role_id', function($q){
            $q->select('id')->from('roles')->whereIn('name', [
                'sales_supervisor',
                'purchase_supervisor',
                'logistics_manager',
            ]);
        });
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['ceo','it_manager']);
    }

    public function purchaseAssignments()
    {
        return $this->hasMany(PurchaseAssignment::class, 'buyer_id');
    }


    public function isManagement(): bool
    {
        return $this->hasRole([
            'ceo',              // مدیر عامل
            'it_manager',       // مدیر انفورماتیک
            'sales_manager',    // مدیر فروش
            'purchase_manager', // مدیر خرید
            'commerce_manager', // مدیر بازرگانی
        ]);
    }

    
    public function canDecideOnPurchaseAssignments(): bool
    {
        return $this->hasAnyRole([
            'ceo',
            'it_manager',
            'commerce_manager',
            'purchase_manager',
            'sales_manager',
        ]);
    }

    public function permissionOverrides()
    {
        return $this->hasMany(PermissionUserOverride::class);
    }


}

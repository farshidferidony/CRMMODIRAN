<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'guard_name']; // اگر ستون label/group هم اضافه کنی، اینجا قرار بده

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public function userOverrides()
    {
        return $this->hasMany(PermissionUserOverride::class);
    }
}
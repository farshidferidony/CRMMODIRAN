<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;

class AccessService
{
    public function userHasPermission(User $user, string $permissionName): bool
    {
        // 1) مدیران کل همیشه دسترسی دارند
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 2) permission را پیدا کن
        $permission = Permission::where('name', $permissionName)->first();

        if (! $permission) {
            // اگر permission ثبت نشده، ترجیحاً false
            return false;
        }

        // 3) override کاربر را چک کن
        $override = $user->permissionOverrides()
            ->where('permission_id', $permission->id)
            ->first();

        if ($override) {
            return (bool) $override->allowed;
        }

        // 4) اگر override نبود، از روی نقش‌ها تصمیم بگیر
        return $user->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('permissions.id', $permission->id);
            })
            ->exists();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class UsersPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مدیریت کاربران
        $pViewUsers = Permission::updateOrCreate(
            ['name' => 'users.view'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده لیست کاربران',
                'group'      => 'users',
            ]
        );

        $pCreateUsers = Permission::updateOrCreate(
            ['name' => 'users.create'],
            [
                'guard_name' => 'web',
                'label'      => 'ایجاد کاربر جدید',
                'group'      => 'users',
            ]
        );

        $pEditUsers = Permission::updateOrCreate(
            ['name' => 'users.edit'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش کاربر',
                'group'      => 'users',
            ]
        );

        $pDeleteUsers = Permission::updateOrCreate(
            ['name' => 'users.delete'],
            [
                'guard_name' => 'web',
                'label'      => 'حذف کاربر',
                'group'      => 'users',
            ]
        );

        // مدیریت نقش‌های کاربران
        $pManageUserRoles = Permission::updateOrCreate(
            ['name' => 'users.roles.manage'],
            [
                'guard_name' => 'web',
                'label'      => 'مدیریت نقش‌های کاربران',
                'group'      => 'users',
            ]
        );

        // مدیریت ماتریکس دسترسی
        $pAccessMatrixManage = Permission::updateOrCreate(
            ['name' => 'access.matrix.manage'],
            [
                'guard_name' => 'web',
                'label'      => 'مدیریت ماتریکس سطوح دسترسی',
                'group'      => 'users',
            ]
        );

        // نقش‌ها
        $ceo      = Role::where('name','ceo')->first();
        $it       = Role::where('name','it_manager')->first();
        $salesMgr = Role::where('name','sales_manager')->first(); // اگر بخواهی به او هم فقط view بدهی

        $allUserPerms = [
            $pViewUsers->id,
            $pCreateUsers->id,
            $pEditUsers->id,
            $pDeleteUsers->id,
            $pManageUserRoles->id,
            $pAccessMatrixManage->id,
        ];

        // ceo و it_manager: full
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching($allUserPerms);
            }
        }

        // مثال: مدیر فروش فقط بتواند لیست کاربران را ببیند
        if ($salesMgr) {
            $salesMgr->permissions()->syncWithoutDetaching([
                $pViewUsers->id,
            ]);
        }
    }
}

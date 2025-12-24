<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class SourcesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // تعریف پرمیشن‌ها برای منابع تامین
        $pViewSources = Permission::updateOrCreate(
            ['name' => 'sources.view'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده لیست منابع تامین',
                'group'      => 'sources',
            ]
        );

        $pCreateSources = Permission::updateOrCreate(
            ['name' => 'sources.create'],
            [
                'guard_name' => 'web',
                'label'      => 'ثبت منبع تامین جدید',
                'group'      => 'sources',
            ]
        );

        $pEditSources = Permission::updateOrCreate(
            ['name' => 'sources.edit'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش منبع تامین',
            'group'      => 'sources',
            ]
        );

        $pDeleteSources = Permission::updateOrCreate(
            ['name' => 'sources.delete'],
            [
                'guard_name' => 'web',
                'label'      => 'حذف منبع تامین',
                'group'      => 'sources',
            ]
        );

        // نقش‌ها
        $ceo          = Role::where('name','ceo')->first();
        $it           = Role::where('name','it_manager')->first();
        $purchaseMgr  = Role::where('name','purchase_manager')->first();
        $buyer        = Role::where('name','buyer')->first();
        $commerceMgr  = Role::where('name','commerce_manager')->first();
        $salesMgr     = Role::where('name','sales_manager')->first();

        $allSourcePerms = [
            $pViewSources->id,
            $pCreateSources->id,
            $pEditSources->id,
            $pDeleteSources->id,
        ];

        // ceo و it_manager: full
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching($allSourcePerms);
            }
        }

        // مدیر خرید و مدیر بازرگانی: full روی منابع
        foreach ([$purchaseMgr, $commerceMgr] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching($allSourcePerms);
            }
        }

        // خریدار: فقط view و شاید ایجاد
        if ($buyer) {
            $buyer->permissions()->syncWithoutDetaching([
                $pViewSources->id,
                $pCreateSources->id,
            ]);
        }

        // مدیر فروش: فقط مشاهده منابع برای اطلاع
        if ($salesMgr) {
            $salesMgr->permissions()->syncWithoutDetaching([
                $pViewSources->id,
            ]);
        }
    }
}

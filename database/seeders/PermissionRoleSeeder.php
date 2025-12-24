<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // نمونه: مشتریان
        $pViewCustomers   = Permission::firstOrCreate(['name' => 'view_customers'], ['guard_name' => 'web']);
        $pCreateCustomers = Permission::firstOrCreate(['name' => 'create_customers'], ['guard_name' => 'web']);
        $pFastSearch      = Permission::firstOrCreate(['name' => 'fast_search_customers'], ['guard_name' => 'web']);

        // نمونه: فاکتورها
        $pViewInvoices    = Permission::firstOrCreate(['name' => 'view_invoices'], ['guard_name' => 'web']);

        // پیدا کردن نقش‌ها
        $ceo      = Role::where('name','ceo')->first();
        $it       = Role::where('name','it_manager')->first();
        $salesMgr = Role::where('name','sales_manager')->first();
        $salesExp = Role::where('name','sales_expert')->first();
        $finMgr   = Role::where('name','finance_manager')->first();
        $finExp   = Role::where('name','finance_expert')->first();

        // مثال: ceo و it به همه این permissionها دسترسی دارند
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching([
                    $pViewCustomers->id,
                    $pCreateCustomers->id,
                    $pFastSearch->id,
                    $pViewInvoices->id,
                ]);
            }
        }

        // مثال: sales_manager
        if ($salesMgr) {
            $salesMgr->permissions()->syncWithoutDetaching([
                $pViewCustomers->id,
                $pCreateCustomers->id,
                $pFastSearch->id,
            ]);
        }

        // مثال: sales_expert
        if ($salesExp) {
            $salesExp->permissions()->syncWithoutDetaching([
                $pViewCustomers->id,
                $pFastSearch->id,
            ]);
        }

        // مثال: مالی
        if ($finMgr) {
            $finMgr->permissions()->syncWithoutDetaching([$pViewInvoices->id]);
        }
        if ($finExp) {
            $finExp->permissions()->syncWithoutDetaching([$pViewInvoices->id]);
        }
    }
}

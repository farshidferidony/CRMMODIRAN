<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class TransportPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) مشاهده و مدیریت کلی فرم‌های حمل
        $pTransportView = Permission::updateOrCreate(
            ['name' => 'transport.view'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده فرم‌های حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportEdit = Permission::updateOrCreate(
            ['name' => 'transport.edit'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش اطلاعات کلی فرم حمل',
                'group'      => 'transport',
            ]
        );

        // 2) مراحل ویزارد داخل پیش‌فاکتور (sales, purchase, logistics_manager, logistics_expert, accounting, sales_manager, logistics_close)

        $pTransportStepSales = Permission::updateOrCreate(
            ['name' => 'transport.step.sales'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش مرحله فروش فرم حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportStepPurchase = Permission::updateOrCreate(
            ['name' => 'transport.step.purchase'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش مرحله خرید فرم حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportStepLogisticsManager = Permission::updateOrCreate(
            ['name' => 'transport.step.logistics_manager'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید/ویرایش مرحله مدیر لجستیک',
                'group'      => 'transport',
            ]
        );

        $pTransportStepLogisticsExpert = Permission::updateOrCreate(
            ['name' => 'transport.step.logistics_expert'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش مرحله کارشناس لجستیک',
                'group'      => 'transport',
            ]
        );

        $pTransportStepAccounting = Permission::updateOrCreate(
            ['name' => 'transport.step.accounting'],
            [
                'guard_name' => 'web',
                'label'      => 'ویرایش مرحله حسابداری حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportStepSalesManager = Permission::updateOrCreate(
            ['name' => 'transport.step.sales_manager'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید مدیر فروش در فرم حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportStepLogisticsClose = Permission::updateOrCreate(
            ['name' => 'transport.step.logistics_close'],
            [
                'guard_name' => 'web',
                'label'      => 'بستن فرم حمل توسط لجستیک',
                'group'      => 'transport',
            ]
        );

        // 3) عملیات حسابداری روی وسایل (vehicles.*) و تسویه کامل
        $pTransportAccountingUpdateVehicle = Permission::updateOrCreate(
            ['name' => 'transport.accounting.update_vehicle'],
            [
                'guard_name' => 'web',
                'label'      => 'ثبت وضعیت حسابداری وسیله حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportAccountingSettleAll = Permission::updateOrCreate(
            ['name' => 'transport.accounting.settle_all'],
            [
                'guard_name' => 'web',
                'label'      => 'تسویه کامل فرم حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportAccountingApproveVehicleFreight = Permission::updateOrCreate(
            ['name' => 'transport.accounting.approve_vehicle'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید کرایه وسیله حمل',
                'group'      => 'transport',
            ]
        );

        $pTransportAccountingRejectVehicleFreight = Permission::updateOrCreate(
            ['name' => 'transport.accounting.reject_vehicle'],
            [
                'guard_name' => 'web',
                'label'      => 'رد کرایه وسیله حمل',
                'group'      => 'transport',
            ]
        );

        // 4) نقش‌ها
        $ceo             = Role::where('name','ceo')->first();
        $it              = Role::where('name','it_manager')->first();
        $logisticsMgr    = Role::where('name','logistics_manager')->first();
        $logisticsExpert = Role::where('name','logistics_expert')->first();
        $salesMgr        = Role::where('name','sales_manager')->first();
        $salesExp        = Role::where('name','sales_expert')->first();
        $finMgr          = Role::where('name','finance_manager')->first();
        $finExp          = Role::where('name','finance_expert')->first();

        $commonViewPerms = [
            $pTransportView->id,
        ];

        $fullTransportPerms = [
            $pTransportView->id,
            $pTransportEdit->id,
            $pTransportStepSales->id,
            $pTransportStepPurchase->id,
            $pTransportStepLogisticsManager->id,
            $pTransportStepLogisticsExpert->id,
            $pTransportStepAccounting->id,
            $pTransportStepSalesManager->id,
            $pTransportStepLogisticsClose->id,
            $pTransportAccountingUpdateVehicle->id,
            $pTransportAccountingSettleAll->id,
            $pTransportAccountingApproveVehicleFreight->id,
            $pTransportAccountingRejectVehicleFreight->id,
        ];

        // ceo و it_manager: همه‌ی حمل‌ونقل
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching($fullTransportPerms);
            }
        }

        // logistics_manager: محور اصلی حمل
        if ($logisticsMgr) {
            $logisticsMgr->permissions()->syncWithoutDetaching([
                $pTransportView->id,
                $pTransportEdit->id,
                $pTransportStepLogisticsManager->id,
                $pTransportStepLogisticsExpert->id,
                $pTransportStepLogisticsClose->id,
                $pTransportAccountingSettleAll->id,
            ]);
        }

        // logistics_expert: ویرایش بخش‌های اجرایی لجستیک
        if ($logisticsExpert) {
            $logisticsExpert->permissions()->syncWithoutDetaching([
                $pTransportView->id,
                $pTransportStepLogisticsExpert->id,
            ]);
        }

        // فروش: دسترسی فقط به مرحله فروش و تایید مدیر فروش
        if ($salesExp) {
            $salesExp->permissions()->syncWithoutDetaching([
                $pTransportView->id,
                $pTransportStepSales->id,
            ]);
        }

        if ($salesMgr) {
            $salesMgr->permissions()->syncWithoutDetaching([
                $pTransportView->id,
                $pTransportStepSales->id,
                $pTransportStepSalesManager->id,
            ]);
        }

        // مالی: مرحله حسابداری و تسویه
        foreach ([$finMgr, $finExp] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching([
                    $pTransportView->id,
                    $pTransportStepAccounting->id,
                    $pTransportAccountingUpdateVehicle->id,
                    $pTransportAccountingSettleAll->id,
                    $pTransportAccountingApproveVehicleFreight->id,
                    $pTransportAccountingRejectVehicleFreight->id,
                ]);
            }
        }
    }
}

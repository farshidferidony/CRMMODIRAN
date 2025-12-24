<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PurchasePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) ارجاع وظایف خرید (purchase-assignments.*)
        $pPurchaseAssignmentsManage = Permission::updateOrCreate(
            ['name' => 'purchase.assignments.manage'],
            [
                'guard_name' => 'web',
                'label'      => 'مدیریت ارجاع وظایف خرید',
                'group'      => 'purchase'
            ]
        );

        // 2) داشبورد خریدار (buyer.assignments.*)
        $pBuyerAssignmentsView = Permission::updateOrCreate(
            ['name' => 'purchase.buyer.assignments.view'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده وظایف خریدار',
                'group'      => 'purchase'
            ]
        );

        $pBuyerAssignmentsEdit = Permission::updateOrCreate(
            ['name' => 'purchase.buyer.assignments.edit'],
            [
                'guard_name' => 'web',
                'label'      => 'به‌روزرسانی وظایف خریدار',
                'group'      => 'purchase'
            ]
        );

        // 3) مدیر خرید (purchase-manager.*)
        $pPurchaseManagerViewPreInvoices = Permission::updateOrCreate(
            ['name' => 'purchase.manager.preinvoices.view'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده پیش‌فاکتورهای خرید برای مدیریت',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseManagerChoosePrices = Permission::updateOrCreate(
            ['name' => 'purchase.manager.choose_prices'],
            [
                'guard_name' => 'web',
                'label'      => 'انتخاب قیمت پیشنهادی تامین‌کنندگان',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseManagerChooseSupplier = Permission::updateOrCreate(
            ['name' => 'purchase.manager.choose_supplier'],
            [
                'guard_name' => 'web',
                'label'      => 'انتخاب تامین‌کننده نهایی برای آیتم',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseManagerApprovePurchasePreInvoice = Permission::updateOrCreate(
            ['name' => 'purchase.manager.preinvoices.approve'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید پیش‌فاکتور خرید توسط مدیر خرید',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseManagerRejectPurchasePreInvoice = Permission::updateOrCreate(
            ['name' => 'purchase.manager.preinvoices.reject'],
            [
                'guard_name' => 'web',
                'label'      => 'رد پیش‌فاکتور خرید توسط مدیر خرید',
                'group'      => 'purchase'
            ]
        );

        // 4) اجرای خرید (purchase_pre_invoices.* با PurchaseExecutionController)
        $pPurchaseExecIndex = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.index'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده لیست پیش‌فاکتورهای خرید (اجرای خرید)',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseExecShow = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.show'],
            [
                'guard_name' => 'web',
                'label'      => 'مشاهده جزئیات پیش‌فاکتور خرید (اجرای خرید)',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseExecFinalizeItem = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.finalize_item'],
            [
                'guard_name' => 'web',
                'label'      => 'نهایی‌سازی آیتم خرید',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseExecApprovePurchase = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.approve_purchase'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید خرید نهایی (پس از اجرا)',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseExecFinalizeItemPurchase = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.finalize_item_purchase'],
            [
                'guard_name' => 'web',
                'label'      => 'نهایی‌سازی خرید تامین‌کننده برای آیتم',
                'group'      => 'purchase'
            ]
        );

        $pPurchaseExecApproveSupplierPayment = Permission::updateOrCreate(
            ['name' => 'purchase.preinvoices.exec.approve_supplier_payment'],
            [
                'guard_name' => 'web',
                'label'      => 'تایید پرداخت تامین‌کننده',
                'group'      => 'purchase'
            ]
        );

        // نقش‌ها
        $ceo        = Role::where('name','ceo')->first();
        $it         = Role::where('name','it_manager')->first();
        $purchaseMgr= Role::where('name','purchase_manager')->first();
        $buyer      = Role::where('name','buyer')->first(); // اگر نقش خریدار را این‌طور نام‌گذاری کرده‌ای
        $commerce   = Role::where('name','commerce_manager')->first();

        $allPurchaseManagerPerms = [
            $pPurchaseAssignmentsManage->id,
            $pPurchaseManagerViewPreInvoices->id,
            $pPurchaseManagerChoosePrices->id,
            $pPurchaseManagerChooseSupplier->id,
            $pPurchaseManagerApprovePurchasePreInvoice->id,
            $pPurchaseManagerRejectPurchasePreInvoice->id,
            $pPurchaseExecIndex->id,
            $pPurchaseExecShow->id,
            $pPurchaseExecFinalizeItem->id,
            $pPurchaseExecApprovePurchase->id,
            $pPurchaseExecFinalizeItemPurchase->id,
            $pPurchaseExecApproveSupplierPayment->id,
        ];

        $buyerPerms = [
            $pBuyerAssignmentsView->id,
            $pBuyerAssignmentsEdit->id,
            $pPurchaseExecIndex->id,
            $pPurchaseExecShow->id,
            $pPurchaseExecFinalizeItem->id,
            $pPurchaseExecFinalizeItemPurchase->id,
        ];

        // ceo و it_manager: همه‌ی خرید
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    array_merge($allPurchaseManagerPerms, $buyerPerms)
                );
            }
        }

        // مدیر خرید: همه‌ی ماژول خرید
        if ($purchaseMgr) {
            $purchaseMgr->permissions()->syncWithoutDetaching(
                array_merge($allPurchaseManagerPerms, $buyerPerms)
            );
        }

        // خریدار: فقط وظایف خود و اجرای خرید
        if ($buyer) {
            $buyer->permissions()->syncWithoutDetaching($buyerPerms);
        }

        // مدیر بازرگانی: فقط نظارت (بدون عملیات تاییدی حساس)
        if ($commerce) {
            $commerce->permissions()->syncWithoutDetaching([
                $pBuyerAssignmentsView->id,
                $pPurchaseManagerViewPreInvoices->id,
                $pPurchaseExecIndex->id,
                $pPurchaseExecShow->id,
            ]);
        }
    }
}

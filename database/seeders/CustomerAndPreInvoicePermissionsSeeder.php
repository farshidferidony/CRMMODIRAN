<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class CustomerAndPreInvoicePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // گروه مشتریان
        $pViewCustomers = Permission::updateOrCreate(
            ['name' => 'customers.view'],
            ['guard_name' => 'web', 'label' => 'مشاهده لیست مشتریان', 'group' => 'customers']
        );

        $pCreateCustomers = Permission::updateOrCreate(
            ['name' => 'customers.create'],
            ['guard_name' => 'web', 'label' => 'ثبت مشتری جدید', 'group' => 'customers']
        );

        $pEditCustomers = Permission::updateOrCreate(
            ['name' => 'customers.edit'],
            ['guard_name' => 'web', 'label' => 'ویرایش مشتری', 'group' => 'customers']
        );

        $pDeleteCustomers = Permission::updateOrCreate(
            ['name' => 'customers.delete'],
            ['guard_name' => 'web', 'label' => 'حذف مشتری', 'group' => 'customers']
        );

        $pFastSearchCustomers = Permission::updateOrCreate(
            ['name' => 'customers.fast_search'],
            ['guard_name' => 'web', 'label' => 'جستجوی سریع مشتری', 'group' => 'customers']
        );

        // گروه پیش‌فاکتور فروش - عملیات پایه
        $pViewPreInvoices = Permission::updateOrCreate(
            ['name' => 'preinvoices.view'],
            ['guard_name' => 'web', 'label' => 'مشاهده لیست پیش‌فاکتورهای فروش', 'group' => 'preinvoices']
        );

        $pCreatePreInvoices = Permission::updateOrCreate(
            ['name' => 'preinvoices.create'],
            ['guard_name' => 'web', 'label' => 'ثبت پیش‌فاکتور فروش', 'group' => 'preinvoices']
        );

        $pEditPreInvoices = Permission::updateOrCreate(
            ['name' => 'preinvoices.edit'],
            ['guard_name' => 'web', 'label' => 'ویرایش پیش‌فاکتور فروش', 'group' => 'preinvoices']
        );

        $pDeletePreInvoices = Permission::updateOrCreate(
            ['name' => 'preinvoices.delete'],
            ['guard_name' => 'web', 'label' => 'حذف پیش‌فاکتور فروش', 'group' => 'preinvoices']
        );

        // عملیات workflow پیش‌فاکتور
        $pSendToPurchase = Permission::updateOrCreate(
            ['name' => 'preinvoices.send_to_purchase'],
            ['guard_name' => 'web', 'label' => 'ارسال به خرید', 'group' => 'preinvoices']
        );

        $pPriceByPurchase = Permission::updateOrCreate(
            ['name' => 'preinvoices.price_by_purchase'],
            ['guard_name' => 'web', 'label' => 'قیمت‌گذاری خرید', 'group' => 'preinvoices']
        );

        $pApprovePurchase = Permission::updateOrCreate(
            ['name' => 'preinvoices.approve_purchase'],
            ['guard_name' => 'web', 'label' => 'تایید مدیر خرید (برای پیش‌فاکتور فروش)', 'group' => 'preinvoices']
        );

        $pPriceBySales = Permission::updateOrCreate(
            ['name' => 'preinvoices.price_by_sales'],
            ['guard_name' => 'web', 'label' => 'قیمت‌گذاری فروش', 'group' => 'preinvoices']
        );

        $pApprovePreInvoicesSalesManager = Permission::updateOrCreate(
            ['name' => 'preinvoices.approve_sales_manager'],
            ['guard_name' => 'web', 'label' => 'تایید مدیر فروش', 'group' => 'preinvoices']
        );

        $pSendToSalesApproval = Permission::updateOrCreate(
            ['name' => 'preinvoices.send_to_sales_approval'],
            ['guard_name' => 'web', 'label' => 'ارسال برای تایید مدیر فروش', 'group' => 'preinvoices']
        );

        $pSendToCustomer = Permission::updateOrCreate(
            ['name' => 'preinvoices.send_to_customer'],
            ['guard_name' => 'web', 'label' => 'ارسال پیش‌فاکتور به مشتری', 'group' => 'preinvoices']
        );

        $pCustomerApprove = Permission::updateOrCreate(
            ['name' => 'preinvoices.customer_approve'],
            ['guard_name' => 'web', 'label' => 'ثبت تایید مشتری', 'group' => 'preinvoices']
        );

        $pCustomerReject = Permission::updateOrCreate(
            ['name' => 'preinvoices.customer_reject'],
            ['guard_name' => 'web', 'label' => 'ثبت عدم تایید مشتری', 'group' => 'preinvoices']
        );

        // نمونه یک گزارش (برای بعد روی reports.invoices.debtors)
        $pViewDebtorsReport = Permission::updateOrCreate(
            ['name' => 'reports.debtors.view'],
            ['guard_name' => 'web', 'label' => 'گزارش بدهکاران', 'group' => 'reports']
        );

        // نقش‌ها
        $ceo      = Role::where('name','ceo')->first();
        $it       = Role::where('name','it_manager')->first();
        $salesMgr = Role::where('name','sales_manager')->first();
        $salesSup = Role::where('name','sales_supervisor')->first();
        $salesExp = Role::where('name','sales_expert')->first();
        $finMgr   = Role::where('name','finance_manager')->first();
        $finExp   = Role::where('name','finance_expert')->first();

        $allCustomerPerms = [
            $pViewCustomers->id,
            $pCreateCustomers->id,
            $pEditCustomers->id,
            $pDeleteCustomers->id,
            $pFastSearchCustomers->id,
        ];

        $allPreInvoicePerms = [
            $pViewPreInvoices->id,
            $pCreatePreInvoices->id,
            $pEditPreInvoices->id,
            $pDeletePreInvoices->id,
            $pSendToPurchase->id,
            $pPriceByPurchase->id,
            $pApprovePurchase->id,
            $pPriceBySales->id,
            $pApprovePreInvoicesSalesManager->id,
            $pSendToSalesApproval->id,
            $pSendToCustomer->id,
            $pCustomerApprove->id,
            $pCustomerReject->id,
        ];

        // ceo و it_manager: همه‌ی این دو گروه + گزارش بدهکاران
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    array_merge($allCustomerPerms, $allPreInvoicePerms, [$pViewDebtorsReport->id])
                );
            }
        }

        // sales_manager: full مشتریان + همه عملیات پیش‌فاکتور فروش
        if ($salesMgr) {
            $salesMgr->permissions()->syncWithoutDetaching(
                array_merge($allCustomerPerms, $allPreInvoicePerms)
            );
        }

        // sales_supervisor: مشاهده/ویرایش مشتری و پیش‌فاکتور، قیمت‌گذاری فروش، ارسال برای تایید
        if ($salesSup) {
            $salesSup->permissions()->syncWithoutDetaching([
                $pViewCustomers->id,
                $pCreateCustomers->id,
                $pEditCustomers->id,
                $pFastSearchCustomers->id,

                $pViewPreInvoices->id,
                $pCreatePreInvoices->id,
                $pEditPreInvoices->id,
                $pSendToPurchase->id,
                $pPriceByPurchase->id,
                $pPriceBySales->id,
                $pSendToSalesApproval->id,
            ]);
        }

        // sales_expert: ایجاد/ویرایش پیش‌فاکتور، قیمت‌گذاری فروش، ارسال به خرید
        if ($salesExp) {
            $salesExp->permissions()->syncWithoutDetaching([
                $pViewCustomers->id,
                $pFastSearchCustomers->id,

                $pViewPreInvoices->id,
                $pCreatePreInvoices->id,
                $pEditPreInvoices->id,
                $pSendToPurchase->id,
                $pPriceBySales->id,
            ]);
        }

        // مالی: مشاهده مشتری + جستجو + گزارش بدهکاران
        foreach ([$finMgr, $finExp] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching([
                    $pViewCustomers->id,
                    $pFastSearchCustomers->id,
                    $pViewDebtorsReport->id,
                ]);
            }
        }
    }
}

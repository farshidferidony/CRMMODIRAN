<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class FinanceAndReportsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) فاکتورها
        $pViewInvoices = Permission::updateOrCreate(
            ['name' => 'invoices.view'],
            ['guard_name' => 'web', 'label' => 'مشاهده لیست فاکتورها', 'group' => 'finance']
        );

        $pViewInvoiceDetail = Permission::updateOrCreate(
            ['name' => 'invoices.view_detail'],
            ['guard_name' => 'web', 'label' => 'مشاهده جزئیات فاکتور', 'group' => 'finance']
        );

        // 2) پیش‌فاکتورهای آماده فاکتور شدن (مالی ← از تایید مدیر فروش)
        $pFinanceFromApprovedSales = Permission::updateOrCreate(
            ['name' => 'finance.preinvoices.from_approved_sales'],
            ['guard_name' => 'web', 'label' => 'پیش‌فاکتورهای تاییدشده برای صدور فاکتور', 'group' => 'finance']
        );

        $pFinanceCreateInvoiceFromPreInvoice = Permission::updateOrCreate(
            ['name' => 'finance.preinvoices.create_invoice'],
            ['guard_name' => 'web', 'label' => 'تبدیل پیش‌فاکتور به فاکتور', 'group' => 'finance']
        );

        // 3) پیش‌فاکتورهای خرید در انتظار مالی
        $pPurchasePreInvoicesWaitingFinance = Permission::updateOrCreate(
            ['name' => 'finance.purchase_preinvoices.view'],
            ['guard_name' => 'web', 'label' => 'خریدهای در انتظار تایید مالی', 'group' => 'finance']
        );

        $pFinanceApprovePurchase = Permission::updateOrCreate(
            ['name' => 'finance.purchase_preinvoices.approve'],
            ['guard_name' => 'web', 'label' => 'تایید مالی پیش‌فاکتور خرید', 'group' => 'finance']
        );

        $pFinanceRejectPurchase = Permission::updateOrCreate(
            ['name' => 'finance.purchase_preinvoices.reject'],
            ['guard_name' => 'web', 'label' => 'رد مالی پیش‌فاکتور خرید', 'group' => 'finance']
        );

        // 4) پرداخت‌ها
        $pViewCustomerPendingPayments = Permission::updateOrCreate(
            ['name' => 'finance.payments.customer_pending'],
            ['guard_name' => 'web', 'label' => 'پرداخت‌های در انتظار مشتریان', 'group' => 'finance']
        );

        $pFinanceConfirmPayment = Permission::updateOrCreate(
            ['name' => 'finance.payments.confirm'],
            ['guard_name' => 'web', 'label' => 'تایید پرداخت مشتری', 'group' => 'finance']
        );

        $pFinanceRejectPayment = Permission::updateOrCreate(
            ['name' => 'finance.payments.reject'],
            ['guard_name' => 'web', 'label' => 'رد پرداخت مشتری', 'group' => 'finance']
        );

        // 5) گزارش‌ها
        $pViewDebtorsReport = Permission::updateOrCreate(
            ['name' => 'reports.debtors.view'],
            ['guard_name' => 'web', 'label' => 'گزارش بدهکاران', 'group' => 'reports']
        );

        $pViewPlansDueReport = Permission::updateOrCreate(
            ['name' => 'reports.plans_due.view'],
            ['guard_name' => 'web', 'label' => 'گزارش اقساط سررسید', 'group' => 'reports']
        );

        // نقش‌ها
        $ceo       = Role::where('name','ceo')->first();
        $it        = Role::where('name','it_manager')->first();
        $finMgr    = Role::where('name','finance_manager')->first();
        $finExp    = Role::where('name','finance_expert')->first();
        $salesMgr  = Role::where('name','sales_manager')->first();
        $commerce  = Role::where('name','commerce_manager')->first();

        $financePerms = [
            $pViewInvoices->id,
            $pViewInvoiceDetail->id,
            $pFinanceFromApprovedSales->id,
            $pFinanceCreateInvoiceFromPreInvoice->id,
            $pPurchasePreInvoicesWaitingFinance->id,
            $pFinanceApprovePurchase->id,
            $pFinanceRejectPurchase->id,
            $pViewCustomerPendingPayments->id,
            $pFinanceConfirmPayment->id,
            $pFinanceRejectPayment->id,
        ];

        $reportsPerms = [
            $pViewDebtorsReport->id,
            $pViewPlansDueReport->id,
        ];

        // ceo و it_manager: همه مالی + همه گزارش‌ها
        foreach ([$ceo, $it] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    array_merge($financePerms, $reportsPerms)
                );
            }
        }

        // مدیر و کارشناس مالی: همه مالی + گزارش‌ها
        foreach ([$finMgr, $finExp] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    array_merge($financePerms, $reportsPerms)
                );
            }
        }

        // مدیر فروش و مدیر بازرگانی: فقط بخش مشاهده فاکتورها و گزارش‌ها
        foreach ([$salesMgr, $commerce] as $role) {
            if ($role) {
                $role->permissions()->syncWithoutDetaching([
                    $pViewInvoices->id,
                    $pViewInvoiceDetail->id,
                    $pViewDebtorsReport->id,
                    $pViewPlansDueReport->id,
                ]);
            }
        }
    }
}

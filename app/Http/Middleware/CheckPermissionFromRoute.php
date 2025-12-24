<?php

namespace App\Http\Middleware;

use App\Services\AccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionFromRoute
{
    public function __construct(protected AccessService $access)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // مهم: اگر لاگین نشده باشد، بگذار middleware auth کار خودش را بکند
        if (! $user) {
            return $next($request);
        }

        $route = $request->route();
        if (! $route) {
            return $next($request);
        }

        $routeName = $route->getName(); // مثل customers.index, pre-invoices.show و ...

        if (! $routeName) {
            return $next($request);
        }

        // نگاشت route_name → permission_name
        $permissionName = $this->mapRouteToPermission($routeName);

        if (! $permissionName) {
            // برای routeهایی که permission تعریف نکردیم، عبور
            return $next($request);
        }

        if (! $this->access->userHasPermission($user, $permissionName)) {
            abort(403, 'شما دسترسی لازم برای این صفحه را ندارید.');
        }

        return $next($request);
    }

    protected function mapRouteToPermission(string $routeName): ?string
    {
        // نگاشت اولیه: هر route به permission مناسب
        // فقط چند نمونه برای شروع؛ بقیه را مشابه اضافه می‌کنی

        // مشتریان
        return match (true) {
            $routeName === 'customers.index'       => 'customers.view',
            $routeName === 'customers.create'      => 'customers.create',
            $routeName === 'customers.store'       => 'customers.create',
            $routeName === 'customers.edit'        => 'customers.edit',
            $routeName === 'customers.update'      => 'customers.edit',
            $routeName === 'customers.destroy'     => 'customers.delete',
            $routeName === 'ajax.customers.search' => 'customers.fast_search',

            // پیش‌فاکتور فروش
            str_starts_with($routeName, 'pre-invoices.') => $this->mapPreInvoiceRoute($routeName),

            // گزارش بدهکاران
            $routeName === 'reports.invoices.debtors' => 'reports.debtors.view',

            // default => null,

             // فاکتورها
            $routeName === 'invoices.index'       => 'invoices.view',
            $routeName === 'invoices.show'        => 'invoices.view_detail',

            // مالی: پیش‌فاکتورهای تاییدشده برای فاکتور
            $routeName === 'finance.pre-invoices.approved-by-sales'   => 'finance.preinvoices.from_approved_sales',
            $routeName === 'finance.pre-invoices.create-invoice'      => 'finance.preinvoices.create_invoice',

            // مالی: خرید در انتظار مالی
            $routeName === 'finance.pre-invoices.purchase-pre-invoices.index'   => 'finance.purchase_preinvoices.view',
            $routeName === 'finance.pre-invoices.purchase-pre-invoices.approve' => 'finance.purchase_preinvoices.approve',
            $routeName === 'finance.pre-invoices.purchase-pre-invoices.reject'  => 'finance.purchase_preinvoices.reject',

            // مالی: پرداخت‌ها
            $routeName === 'finance.payments.customer.pending' => 'finance.payments.customer_pending',
            $routeName === 'finance.payments.confirm'          => 'finance.payments.confirm',
            $routeName === 'finance.payments.reject'           => 'finance.payments.reject',

            // گزارش‌ها
            $routeName === 'reports.invoices.debtors' => 'reports.debtors.view',
            $routeName === 'reports.plans.due'        => 'reports.plans_due.view',

             // Purchase assignments
            str_starts_with($routeName, 'purchase_assignments.') => match ($routeName) {
                'purchase_assignments.store',
                'purchase_assignments.change_buyer',
                'purchase_assignments.change_source' => 'purchase.assignments.manage',
                default => null,
            },

            // Buyer dashboard
            str_starts_with($routeName, 'buyer.assignments.') => match ($routeName) {
                'buyer.assignments.index'  => 'purchase.buyer.assignments.view',
                'buyer.assignments.edit',
                'buyer.assignments.update' => 'purchase.buyer.assignments.edit',
                default => null,
            },

            // Purchase manager
            str_starts_with($routeName, 'purchase-manager.') => match ($routeName) {
                'purchase-manager.pre-invoices.ready-for-sales',
                'purchase-manager.pre-invoices.review'        => 'purchase.manager.preinvoices.view',
                'purchase-manager.pre-invoices.choose-prices' => 'purchase.manager.choose_prices',
                'purchase-manager.purchase-manager.assignments.choose' => 'purchase.manager.choose_supplier',
                'purchase-manager.purchase-pre-invoices.approve'       => 'purchase.manager.preinvoices.approve',
                'purchase-manager.purchase-pre-invoices.reject'        => 'purchase.manager.preinvoices.reject',
                default => null,
            },

            // Purchase pre-invoices (execution)
            str_starts_with($routeName, 'purchase_pre_invoices.') => match ($routeName) {
                'purchase_pre_invoices.index'               => 'purchase.preinvoices.exec.index',
                'purchase_pre_invoices.purchase_show',
                'purchase_pre_invoices.show'                => 'purchase.preinvoices.exec.show',
                'purchase_pre_invoices.items.finalize',
                'purchase_pre_invoices.items.finalize_purchase' => 'purchase.preinvoices.exec.finalize_item',
                'purchase_pre_invoices.approve_purchase'        => 'purchase.preinvoices.exec.approve_purchase',
                'purchase_pre_invoices.approve_supplier_payment'=> 'purchase.preinvoices.exec.approve_supplier_payment',
                default => null,
            },

            
            // Transport from pre-invoices wizard
            str_starts_with($routeName, 'pre_invoices.transports.') => $this->mapPreInvoiceTransportRoute($routeName),

            // Transport standalone routes
            str_starts_with($routeName, 'transports.') => $this->mapTransportRoute($routeName),

             // منابع تامین
            str_starts_with($routeName, 'sources.') => match ($routeName) {
                'sources.index',
                'sources.show'   => 'sources.view',

                'sources.create',
                'sources.store'  => 'sources.create',

                'sources.edit',
                'sources.update' => 'sources.edit',

                'sources.destroy' => 'sources.delete',

                default => null,
            },

             // Users (resource)
            str_starts_with($routeName, 'users.') => $this->mapUsersRoute($routeName),

            // Access matrix
            str_starts_with($routeName, 'access.matrix') => 'access.matrix.manage',

            default => $this->mapPreInvoiceRoute($routeName),


        };
    }

    
    protected function mapUsersRoute(string $routeName): ?string
    {
        return match ($routeName) {
            'users.index',
            'users.show'   => 'users.view',

            'users.create',
            'users.store'  => 'users.create',

            'users.edit',
            'users.update' => 'users.edit',

            'users.destroy' => 'users.delete',

            'users.roles.edit',
            'users.roles.update' => 'users.roles.manage',

            default => null,
        };
    }

    protected function mapPreInvoiceRoute(string $routeName): ?string
    {
        // routeهای گروه pre-invoices و pre_invoices.*
        return match ($routeName) {
            'pre-invoices.index', 'pre_invoices.index'          => 'preinvoices.view',
            'pre-invoices.create', 'pre_invoices.create'        => 'preinvoices.create',
            'pre-invoices.store', 'pre_invoices.store'          => 'preinvoices.create',
            'pre-invoices.show', 'pre_invoices.show'            => 'preinvoices.view',
            'pre-invoices.edit', 'pre_invoices.edit'            => 'preinvoices.edit',
            'pre-invoices.update', 'pre_invoices.update'        => 'preinvoices.edit',
            'pre-invoices.destroy', 'pre_invoices.destroy'      => 'preinvoices.delete',

            // عملیات workflow
            'pre_invoices.send_to_purchase'         => 'preinvoices.send_to_purchase',
            'pre_invoices.price_by_purchase'        => 'preinvoices.price_by_purchase',
            'pre_invoices.approve_purchase'         => 'preinvoices.approve_purchase',
            'pre_invoices.price_by_sales'           => 'preinvoices.price_by_sales',
            'pre_invoices.approve_sales'            => 'preinvoices.approve_sales',
            'pre_invoices.send_to_sales_approval'   => 'preinvoices.send_to_sales_approval',
            'pre_invoices.sales_approve'            => 'preinvoices.approve_sales_manager',
            'pre_invoices.sales_reject'             => 'preinvoices.reject_sales_manager',
            'pre_invoices.send_to_customer'         => 'preinvoices.send_to_customer',
            'pre_invoices.accept_by_customer'       => 'preinvoices.customer_approve',
            'pre_invoices.reject_by_customer'       => 'preinvoices.customer_reject',

            'pre_invoices.save_sale_prices'         => 'preinvoices.price_by_sales',
            'pre_invoices.edit_sale_prices'         => 'preinvoices.price_by_sales',

            default => null,
        };
    }

    
    protected function mapPreInvoiceTransportRoute(string $routeName): ?string
    {
        return match ($routeName) {
            'pre_invoices.transports.index',
            'pre_invoices.transports.wizard.show'                 => 'transport.view',

            'pre_invoices.transports.store'                       => 'transport.edit',

            'pre_invoices.transports.wizard.update.sales'         => 'transport.step.sales',
            'pre_invoices.transports.wizard.update.purchase'      => 'transport.step.purchase',
            'pre_invoices.transports.wizard.update.logistics_manager' => 'transport.step.logistics_manager',
            'pre_invoices.transports.wizard.update.logistics_expert'  => 'transport.step.logistics_expert',
            'pre_invoices.transports.wizard.update.accounting'        => 'transport.step.accounting',
            'pre_invoices.transports.wizard.update.sales_manager'     => 'transport.step.sales_manager',
            'pre_invoices.transports.wizard.update.logistics_close'   => 'transport.step.logistics_close',

            default => null,
        };
    }

    protected function mapTransportRoute(string $routeName): ?string
    {
        return match ($routeName) {
            'transports.edit',
            'transports.update'                                => 'transport.edit',

            'transports.vehicles.accounting-approve'           => 'transport.accounting.approve_vehicle',
            'transports.vehicles.accounting-reject'            => 'transport.accounting.reject_vehicle',
            'transports.vehicles.settle'                       => 'transport.accounting.settle_all', // یا اگر بخواهی جدا تعریف کنی

            'transports.settle-all',
            'transports.accounting.settleAll'                  => 'transport.accounting.settle_all',

            'transports.accounting.updateVehicle'              => 'transport.accounting.update_vehicle',

            'transports.sales-manager.approve'                 => 'transport.step.sales_manager',
            'transports.close'                                 => 'transport.step.logistics_close',

            default => null,
        };
    }
}

<?php
namespace App\Policies;


use App\Models\PreInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PreInvoicePolicy
{
    // ساخت پیش‌فاکتور: کارشناسان و مدیران فروش + مدیرکل/IT
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasRole([
            'sales_expert',
            'sales_supervisor',
            'sales_manager',
            'commerce_manager',
        ]);
    }

    // دیدن هر پیش‌فاکتور
    public function view(User $user, PreInvoice $preInvoice): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // فعلاً ساده: هر کسی که نقشش در فروش/بازرگانی است
        if ($user->hasRole([
            'sales_expert',
            'sales_supervisor',
            'sales_manager',
            'commerce_manager',
        ])) {
            return true;
        }

        return false;
    }

    // ویرایش: فقط سازنده + مدیران + سرپرست مربوط به دسته کالا
    public function update(User $user, PreInvoice $preInvoice): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // اگر خود سازنده است
        if ($user->id === $preInvoice->created_by) {
            return true;
        }

        // اگر مدیر فروش/بازرگانی است
        if ($user->hasRole(['sales_manager','commerce_manager'])) {
            return true;
        }

        // اگر سرپرست فروش دسته‌های آیتم‌هاست
        return $this->isSalesSupervisorOfPreInvoiceCategories($user, $preInvoice);
    }

    // متد کمکی: آیا کاربر سرپرست فروش دسته سرِ آیتم‌هاست؟
    protected function isSalesSupervisorOfPreInvoiceCategories(User $user, PreInvoice $preInvoice): bool
    {
        // اگر اصلاً نقش سرپرست فروش ندارد، سریع false
        if (! $user->hasRole('sales_supervisor')) {
            return false;
        }

        // آیدی سر‌دسته‌هایی که این کاربر سرپرست‌شان است
        $supervisedCategoryIds = $user->categoryRoles()
            ->wherePivotIn('role_id', function($q) {
                $q->select('id')->from('roles')
                  ->where('name','sales_supervisor');
            })
            ->pluck('product_categories.id')
            ->toArray();

        if (empty($supervisedCategoryIds)) {
            return false;
        }

        // دسته‌های آیتم‌های پیش‌فاکتور
        $itemCategoryIds = $preInvoice->items()
            ->with('product.category.parent')
            ->get()
            ->map(function ($item) {
                $cat = $item->product?->category;
                // سر‌دسته را پیدا کن (تا بالا برو)
                while ($cat && $cat->parent) {
                    $cat = $cat->parent;
                }
                return $cat?->id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // اگر هرکدام از سر‌دسته‌های آیتم‌ها در لیست supervisedCategoryIds بود، اجازه بده
        return ! empty(array_intersect($itemCategoryIds, $supervisedCategoryIds));
    }
}

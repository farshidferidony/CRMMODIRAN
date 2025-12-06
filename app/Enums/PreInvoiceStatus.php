<?php

namespace App\Enums;

enum PreInvoiceStatus:string
{
    case Draft                   = 'draft';
    case WaitingPurchase         = 'waiting_purchase';
    case ApprovedManager         = 'approved_manager';
    case Confirmed               = 'confirmed';
    case Rejected                = 'rejected';
    case Closed                  = 'closed';

    case PricedByPurchase        = 'priced_by_purchase';
    case PricedBySales           = 'priced_by_sales';
    case WaitingSalesApproval    = 'waiting_sales_approval';
    case ApprovedBySalesManager  = 'approved_by_sales_manager';
    case RejectedBySalesManager  = 'rejected_by_sales_manager';

    case WaitingFinancePurchase  = 'waiting_finance_purchase';
    case FinancePurchaseApproved = 'finance_purchase_approved';
    case FinancePurchaseRejected = 'finance_purchase_rejected';

    // وضعیت‌های جدید مربوط به مشتری
    case CustomerApproved        = 'customer_approved';
    case CustomerRejected        = 'customer_rejected';

    public function label(): string
    {
        return match($this) {
            self::Draft                   => 'پیش‌نویس',
            self::WaitingPurchase         => 'در انتظار خرید',
            self::ApprovedManager         => 'تایید مدیر خرید',
            self::Confirmed               => 'تایید شده',
            self::Rejected                => 'رد شده',
            self::Closed                  => 'بسته شده',

            self::PricedByPurchase        => 'قیمت‌گذاری توسط خرید',
            self::PricedBySales           => 'قیمت‌گذاری توسط فروش',
            self::WaitingSalesApproval    => 'در انتظار تایید مدیر فروش',
            self::ApprovedBySalesManager  => 'تایید مدیر فروش',
            self::RejectedBySalesManager  => 'رد توسط مدیر فروش',

            self::WaitingFinancePurchase  => 'انتظار تامین مالی خرید',
            self::FinancePurchaseApproved => 'خرید مالی تایید شد',
            self::FinancePurchaseRejected => 'خرید مالی رد شد',

            self::CustomerApproved        => 'تایید توسط مشتری',
            self::CustomerRejected        => 'عدم تایید مشتری',
        };
    }

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}

<?php

namespace App\Enums;

enum TransportStatus: string
{
    case RequestedBySales      = 'requested_by_sales';
    case CompletedBySales      = 'completed_by_sales';
    case CompletedByPurchase   = 'completed_by_purchase';
    case AssignedToLogistics   = 'assigned_to_logistics';
    case InProgressByTransport = 'in_progress';
    case VehicleLoaded         = 'vehicle_loaded';
    case Shipped               = 'shipped';
    case Delivered             = 'delivered';
    case CheckedByAccounting   = 'checked_by_accounting';
    case CheckedBySalesManager = 'checked_by_sales_manager';
    case Closed                = 'closed';

    // جدیدها
    case LogisticsCompleted    = 'logistics_completed';
    case AccountingApproved    = 'accounting_approved';
    case SalesManagerApproved  = 'sales_manager_approved';

    public function label(): string
    {
        return match ($this) {
            self::RequestedBySales      => 'درخواست شده توسط کارشناس فروش',
            self::CompletedBySales      => 'تکمیل شده توسط کارشناس فروش',
            self::CompletedByPurchase   => 'تکمیل شده توسط کارشناسان خرید',
            self::AssignedToLogistics   => 'ارجاع به لجستیک / کارشناس حمل',
            self::InProgressByTransport => 'در حال پیگیری حمل',
            self::VehicleLoaded         => 'بارگیری شده',
            self::Shipped               => 'ارسال شده',
            self::Delivered             => 'تحویل و تخلیه شده',
            self::CheckedByAccounting   => 'بررسی شده توسط حسابداری',
            self::CheckedBySalesManager => 'بررسی شده توسط مدیر فروش',
            self::Closed                => 'بسته شده',

            self::LogisticsCompleted    => 'پایان عملیات حمل',
            self::AccountingApproved    => 'تایید شده توسط حسابداری',
            self::SalesManagerApproved  => 'تایید شده توسط مدیر فروش',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}

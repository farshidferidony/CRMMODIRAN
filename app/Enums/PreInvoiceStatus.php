<?php

namespace App\Enums;


enum PreInvoiceStatus:string
{
    
    // 4. پیش‌فاکتور توسط کارشناس فروش
    case Draft                   = 'draft';

    // 5–7. ارجاع به خرید و قیمت‌گذاری خرید
    case WaitingPurchase         = 'waiting_purchase';          // ارجاع به مدیر/کارشناس خرید
    case PricedByPurchase        = 'priced_by_purchase';        // قیمت‌گذاری توسط خرید
    case ApprovedManager         = 'approved_manager';          // تایید نهایی مدیر خرید

    // 8–11. قیمت‌گذاری و تایید فروش
    case PricedBySales           = 'priced_by_sales';           // درج قیمت توسط کارشناس فروش
    case WaitingSalesApproval    = 'waiting_sales_approval';    // ارسال برای مدیر فروش
    case ApprovedBySalesManager  = 'approved_by_sales_manager'; // تایید مدیر فروش
    case RejectedBySalesManager  = 'rejected_by_sales_manager'; // عدم تایید مدیر فروش

    // 10–11. تایید/عدم تایید مالی قبل از ارسال به مشتری
    case WaitingFinancePurchase  = 'waiting_finance_purchase';  // در انتظار تایید مالی پیش‌فاکتور
    case FinancePurchaseApproved = 'finance_purchase_approved'; // تایید مالی (اجازه چاپ و ارسال)
    case FinancePurchaseRejected = 'finance_purchase_rejected'; // رد مالی

    // 12–13. ارسال به مشتری و پاسخ مشتری
    case Confirmed               = 'confirmed';                 // پیش‌فاکتور نهایی شده و چاپ‌شده
    case CustomerApproved        = 'customer_approved';         // تایید مشتری
    case CustomerRejected        = 'customer_rejected';         // عدم تایید مشتری

    // 14–15. دریافت/تایید پیش‌پرداخت یا مبلغ کل
    case AdvanceWaitingFinance   = 'advance_waiting_finance';   // در انتظار تایید مالی پیش‌پرداخت/کل
    case AdvanceFinanceApproved  = 'advance_finance_approved';  // پیش‌پرداخت/کل تایید مالی شد
    case AdvanceFinanceRejected  = 'advance_finance_rejected';  // پیش‌پرداخت/کل توسط مالی رد شد

    // 16–20. شروع خرید واقعی و تکمیل آن
    case WaitingPurchaseExecution = 'waiting_purchase_execution'; // ارجاع رسمی به کارشناسان خرید تاییدشده
    case Purchasing               = 'purchasing';                 // خرید در حال انجام است
    case PurchaseCompleted        = 'purchase_completed';         // خرید کل آیتم‌ها تکمیل شده (۱۸–۲۰)

    // 21–29. حمل، تخلیه و تاییدهای فروش
    // 21–22. تایید فروش بعد از خرید و درخواست حمل
    case PostPurchaseSalesApproved = 'post_purchase_sales_approved'; // تایید کارشناس فروش بعد از تکمیل خرید
    case ShippingRequested         = 'shipping_requested';           // درخواست فرم حمل توسط کارشناس فروش
    case ShippingPrepared         = 'shipping_prepared';          // فرم حمل تنظیم شده (۲۱–۲۲)
    case ShippingInProgress       = 'shipping_in_progress';       // در حال حمل / بارگیری (۲۳–۲۶)
    case Delivered                = 'delivered';                  // تخلیه و تایید شده (۲۷–۲۹)

    // 30–33. تبدیل به فاکتور و تسویه
    case Invoiced                 = 'invoiced';                   // تبدیل به فاکتور فروش (۳۰)
    case Closed                   = 'closed';                     // تسویه کامل و بستن پیش‌فاکتور (۳۱–۳۳)

    // وضعیت کلی رد شدن
    case Rejected                 = 'rejected';                   // رد کلی در هر مرحله


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
            
            self::AdvanceWaitingFinance  => 'در انتظار تایید مالی پیش‌پرداخت',
            self::AdvanceFinanceApproved => 'پیش‌پرداخت تایید مالی شد',
            self::AdvanceFinanceRejected => 'پیش‌پرداخت توسط مالی رد شد',

            self::WaitingPurchaseExecution => 'ارجاع به کارشناسان خرید',
            self::Purchasing               => 'خرید در حال انجام',
            self::PurchaseCompleted        => 'خرید تکمیل شده',
            
            self::PostPurchaseSalesApproved => 'تایید شرایط توسط کارشناس فروش (بعد از خرید)',
            self::ShippingRequested         => 'درخواست فرم حمل',
            self::ShippingPrepared          => 'آماده حمل',
            self::ShippingInProgress        => 'در حال حمل',
            self::Delivered                 => 'تخلیه و تایید شده',
            
            self::Invoiced                 => 'تبدیل به فاکتور',

        };
    }

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\PreInvoiceStatus;



class PreInvoice extends Model
{
    use SoftDeletes, LogsActivity;
    // protected $fillable = ['customer_id','source_id','type','status','total_amount','formal_extra','created_by'];
     protected $fillable = [
        'direction',
        'sale_pre_invoice_id',
        'source_id',
        'buyer_id',
        'customer_id',
        'type',
        'status',
        'total_amount',
        'formal_extra',
        'created_by'
    ];
    // public function items() { return $this->hasMany(PreInvoiceItem::class); }
    public function items()
    {
        return $this->hasMany(PreInvoiceItem::class, 'pre_invoice_id');
    }

    public function saleItems()
    {
        // آیتم‌های متصل به پیش‌فاکتور فروش
        return $this->hasMany(PreInvoiceItem::class, 'pre_invoice_id');
    }

    public function purchaseItems()
    {
        // آیتم‌هایی که این پیش‌فاکتور خرید، مالک آن‌هاست
        return $this->hasMany(PreInvoiceItem::class, 'purchase_pre_invoice_id');
    }

    // آیتم‌های خرید
    // public function purchaseItems()
    // {
    //     return $this->hasMany(PreInvoiceItem::class, 'pre_invoice_id')
    //         ->whereHas('preInvoice', fn($q) => $q->where('direction', 'purchaseItems'));
    // }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function source() { return $this->belongsTo(Source::class); }

    protected static $logAttributes = [
        'direction',
        'sale_pre_invoice_id',
        'source_id',
        'buyer_id',
        'customer_id',
        'type',
        'status',
        'total_amount',
        'formal_extra',
        'created_by'
    ];
    protected static $logName = 'PreInvoice';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('User');
    }

    public function purchaseLinksAsSale()
    {
        return $this->hasMany(\App\Models\SalePurchasePreInvoice::class, 'sale_pre_invoice_id');
    }

    public function purchaseLinksAsPurchase()
    {
        return $this->hasMany(\App\Models\SalePurchasePreInvoice::class, 'purchase_pre_invoice_id');
    }

    public function scopeSales($q)
    {
        return $q->where('direction','sale');
    }

    public function scopePurchases($q)
    {
        return $q->where('direction','purchase');
    }


    protected $casts = [
        'status' => PreInvoiceStatus::class,
    ];

    public function isReadyForCustomer(): bool
    {
        // فقط اگر فروش قیمت‌گذاری کرده و بعد از آن
        return in_array($this->status, [
            \App\Enums\PreInvoiceStatus::PricedBySales,
            \App\Enums\PreInvoiceStatus::WaitingSalesApproval,
            \App\Enums\PreInvoiceStatus::ApprovedBySalesManager,
            \App\Enums\PreInvoiceStatus::Confirmed,
        ], true);
    }


    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    // نمونه‌ ساده‌ی مجاز بودن ترنزیشن‌ها

    public function canSendToPurchase(): bool
    {
        // 4 → 5
        return $this->status === PreInvoiceStatus::Draft;
    }

    public function canBePricedByPurchase(): bool
    {
        // waiting_purchase → priced_by_purchase
        return $this->status === PreInvoiceStatus::WaitingPurchase;
    }

    public function canApproveByPurchaseManager(): bool
    {
        // priced_by_purchase → approved_manager
        return $this->status === PreInvoiceStatus::PricedByPurchase;
    }

    // public function canBePricedBySales(): bool
    // {
    //     // approved_manager → priced_by_sales
    //     return $this->status === PreInvoiceStatus::ApprovedManager;
    // }

    public function canBePricedBySales(): bool
    {
        // approved_manager → priced_by_sales
        return $this->status === \App\Enums\PreInvoiceStatus::ApprovedManager
            && $this->allPurchaseApprovedByFinance();
    }

    public function canSendToSalesApproval(): bool
    {
        // priced_by_sales → waiting_sales_approval
        return $this->status === PreInvoiceStatus::PricedBySales;
    }

    public function canSalesApproveOrReject(): bool
    {
        // waiting_sales_approval → approved_by_sales_manager / rejected_by_sales_manager
        return $this->status === PreInvoiceStatus::WaitingSalesApproval;
    }

    public function canSendToCustomer(): bool
    {
        // approved_by_sales_manager → confirmed (ارسال به مشتری)
        return $this->status === PreInvoiceStatus::ApprovedBySalesManager;
    }

    // public function salePreInvoice()
    // {
    //     return $this->belongsTo(self::class, 'sale_pre_invoice_id');
    // }

    // پیش‌فاکتور خرید، لینک به پیش‌فاکتور فروش اصلی
    public function salePreInvoice()
    {
        return $this->belongsTo(PreInvoice::class, 'sale_pre_invoice_id')
            ->where('direction', 'sale');
    }

    // public function purchasePreInvoices()
    // {
    //     return $this->hasMany(self::class, 'sale_pre_invoice_id')
    //         ->where('direction','purchase');
    // }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // public function purchasePreInvoices()
    // {
    //     return $this->hasMany(PreInvoice::class, 'sale_pre_invoice_id')
    //         ->where('direction', 'purchase');
    // }

    // public function purchasePreInvoices()
    // {
    //     return $this->hasMany(self::class, 'sale_pre_invoice_id')
    //         ->where('direction', 'purchase');
    // }
    // پیش‌فاکتور فروش، لیست پیش‌فاکتورهای خرید مرتبط
    public function purchasePreInvoices()
    {
        return $this->hasMany(PreInvoice::class, 'sale_pre_invoice_id')
            ->where('direction', 'purchase');
    }


    public function hasActivePurchasePreInvoices(): bool
    {
        return $this->purchasePreInvoices()
            ->whereNotIn('status', [
                \App\Enums\PreInvoiceStatus::FinancePurchaseApproved,
                \App\Enums\PreInvoiceStatus::FinancePurchaseRejected,
            ])
            ->count() > 0;
    }


    public function allPurchaseApprovedByFinance(): bool
    {
        if ($this->purchasePreInvoices()->count() === 0) {
            return false;
        }

        return $this->purchasePreInvoices()
            ->where('status', '!=', \App\Enums\PreInvoiceStatus::FinancePurchaseApproved)
            ->count() === 0;
    }


    public function canSetCustomerDecision(): bool
    {
        // فقط بعد از تایید مدیر فروش و ارسال به مشتری
        return $this->status === \App\Enums\PreInvoiceStatus::Confirmed;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'pre_invoice_id');
    }


    public function customerPayments()
    {
        // فعلا همه پرداخت‌های مرتبط با این پیش‌فاکتور را به‌عنوان پرداخت مشتری می‌گیریم
        return $this->hasMany(Payment::class, 'pre_invoice_id');
    }

    public function confirmedPaidAmount(): float
    {
        return (float) $this->payments()
            ->where('status', 'confirmed')
            ->sum(\DB::raw('COALESCE(paid_amount, amount)'));
    }

    /**
    * مبلغی که برای فعال‌شدن مرحله بعد لازم است.
    * اگر کل فاکتور ملاک است: total_amount + formal_extra (در صورت رسمی بودن).
    */
    public function requiredFinanceAmount(): float
    {
        $base = (float) $this->total_amount;

        if ($this->type === 'formal' && $this->formal_extra) {
            $base += (float) $this->formal_extra;
        }

        return $base;
    }

    /**
    * آیا پرداخت لازم تایید شده است؟
    * اگر فقط «پیش‌پرداخت» ملاک باشد، اینجا می‌توانی منطق دیگری قرار بدهی.
    */
    public function hasRequiredPaymentConfirmed(): bool
    {
        return $this->confirmedPaidAmount() >= $this->requiredFinanceAmount();
    }

    // public function paymentPlans()
    // {
    //     return $this->hasMany(InvoicePaymentPlan::class);
    // }

    public function paymentPlans()
    {
        return $this->hasMany(InvoicePaymentPlan::class, 'pre_invoice_id');
    }


    public function pendingFinancePaymentsAmount(): float
    {
        return (float) $this->payments()
            ->where('status', 'pending')
            ->sum(\DB::raw('COALESCE(paid_amount, amount)'));
    }

    public function hasAdvancePaidPendingFinance(): bool
    {
        // اینجا می‌توانی به‌جای total_amount، فیلد خاص پیش‌پرداخت را در نظر بگیری
        $required = (float) ($this->required_advance_amount ?? 0);

        return $required > 0
            ? $this->pendingFinancePaymentsAmount() >= $required
            : $this->pendingFinancePaymentsAmount() > 0;
    }

     // حداقل یک پرداخت تایید شده است؟
    public function hasConfirmedPayments(): bool
    {
        return $this->payments()
            ->where('status', 'confirmed')
            ->exists();
    }

    // مجموع پرداخت‌های تایید شده
    public function confirmedPaymentsSum(): float
    {
        return (float) $this->payments()
            ->where('status', 'confirmed')
            ->sum(DB::raw('COALESCE(paid_amount, amount)'));
    }

    // پرداخت‌های مرتبط برای نمایش در همان صفحه
    public function paymentsForFinance()
    {
        return $this->payments()
            ->orderByDesc('id')
            ->get();
    }


    public function plans()
    {
        return $this->hasMany(\App\Models\InvoicePaymentPlan::class, 'pre_invoice_id');
    }

    public function hasPaymentPlan(): bool
    {
        return $this->plans()->count() > 0;
    }

    public function canBeConvertedToInvoice(): bool
    {
        return $this->status === \App\Enums\PreInvoiceStatus::CustomerApproved
            && $this->hasPaymentPlan()
            && $this->allPurchaseApprovedByFinance(); // از قبل داریم
    }



    // public function canSendToPurchase(): bool
    // {
    //     return $this->status === PreInvoiceStatus::Draft;
    // }

    // public function canBePricedByPurchase(): bool
    // {
    //     return $this->status === PreInvoiceStatus::SentToPurchase;
    // }

    // public function canApproveByPurchaseManager(): bool
    // {
    //     return $this->status === PreInvoiceStatus::PricedByPurchase;
    // }

    // public function canBePricedBySales(): bool
    // {
    //     return $this->status === PreInvoiceStatus::ApprovedByPurchaseManager;
    // }

    // public function canApproveBySalesManager(): bool
    // {
    //     return $this->status === PreInvoiceStatus::PricedBySales;
    // }

    // public function canSendToCustomer(): bool
    // {
    //     return $this->status === PreInvoiceStatus::ApprovedBySalesManager;
    // }

    // public function canAcceptOrRejectByCustomer(): bool
    // {
    //     return $this->status === PreInvoiceStatus::SentToCustomer;
    // }

}

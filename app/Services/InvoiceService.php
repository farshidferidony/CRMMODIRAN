<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PreInvoice;
use App\Enums\PreInvoiceStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * تبدیل یک پیش‌فاکتور (خرید یا فروش) به فاکتور
     */
    public function convertPreInvoiceToInvoice(PreInvoice $preInvoice): Invoice
    {
        return DB::transaction(function () use ($preInvoice) {

            // 1) ساخت فاکتور (kind بر اساس direction)
            $invoice = Invoice::create([
                'customer_id'   => $preInvoice->customer_id,
                'type'          => $preInvoice->type,
                'status'        => 'awaiting_payment', // در ENUM فاکتور داری
                'total_amount'  => $preInvoice->total_amount,
                'formal_extra'  => $preInvoice->formal_extra,
                'created_by'    => Auth::id(),
                'kind'          => $preInvoice->direction === 'purchase' ? 'purchase' : 'sale',
            ]);

            // 2) انتقال آیتم‌های پیش‌فاکتور به آیتم‌های فاکتور
            foreach ($preInvoice->items as $piItem) {

                $unitPrice = $preInvoice->direction === 'purchase'
                    ? ($piItem->purchase_unit_price ?? $piItem->unit_price)
                    : ($piItem->sale_unit_price ?? $piItem->unit_price);

                $invoice->items()->create([
                    'product_id' => $piItem->product_id,
                    'quantity'   => $piItem->quantity,
                    'unit_price' => $unitPrice,
                    'attributes' => $piItem->attributes,
                    'total'      => $piItem->total,
                ]);
            }

            // 3) لینک کردن حمل به فاکتور
            $preInvoice->transports()->update([
                'invoice_id' => $invoice->id,
            ]);

            // 4) لینک کردن پلن‌های پرداخت به فاکتور
            $preInvoice->paymentPlans()->update([
                'invoice_id' => $invoice->id,
            ]);

            // 5) لینک کردن پرداخت‌ها به فاکتور
            $preInvoice->payments()->update([
                'invoice_id' => $invoice->id,
            ]);

            // 6) به‌روزرسانی خود PreInvoice
            $preInvoice->update([
                'status' => PreInvoiceStatus::Invoiced->value,
                // اگر در جدول pre_invoices این فیلدها را اضافه کرده‌ای، این‌ها را هم باز کن:
                // 'invoice_id'           => $invoice->id,
                // 'converted_to_invoice' => true,
                // 'converted_at'         => now(),
                // 'converted_by'         => Auth::id(),
            ]);

            return $invoice;
        });
    }
}

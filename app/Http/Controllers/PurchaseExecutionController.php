<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\PreInvoiceItem;
use App\Enums\PreInvoiceStatus;
use App\Models\PurchaseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseExecutionController extends Controller
{
    // تغییر منبع پیش‌فاکتور خرید
    // public function changeSource(Request $request, PreInvoice $purchasePreInvoice)
    // {
    //     // abort_unless($purchasePreInvoice->direction === 'purchase', 404);

    //     $data = $request->validate([
    //         'source_id' => ['required', 'exists:sources,id'],
    //     ]);

    //     $purchasePreInvoice->update([
    //         'source_id' => $data['source_id'],
    //     ]);

    //     return back()->with('success', 'منبع این پیش‌فاکتور خرید تغییر کرد.');
    // }

    // // تغییر کارشناس خرید
    // public function changeBuyer(Request $request, PreInvoice $purchasePreInvoice)
    // {
    //     // abort_unless($purchasePreInvoice->direction === 'purchase', 404);

    //     $data = $request->validate([
    //         'buyer_id' => ['required', 'exists:users,id'],
    //     ]);
    //     dd($data);
    //     $purchasePreInvoice->update([
    //         'buyer_id' => $data['buyer_id'],
    //     ]);

    //     return back()->with('success', 'کارشناس خرید این پیش‌فاکتور تغییر کرد.');
    // }

    // تغییر کارشناس خرید برای یک assignment
    // public function changeBuyer(Request $request, PurchaseAssignment $assignment)
    // {
    //     $data = $request->validate([
    //         'buyer_id' => ['required', 'exists:users,id'],
    //     ]);
    //     // dd($assignment);
    //     $assignment->update([
    //         'buyer_id' => $data['buyer_id'],
    //         'status'   => 'assigned', // یا اگر منطق دیگری داری
    //     ]);

    //     return back()->with('success', 'کارشناس خرید برای این آیتم تغییر کرد.');
    // }

    // // تغییر منبع برای یک assignment
    // public function changeSource(Request $request, PurchaseAssignment $assignment)
    // {
    //     $data = $request->validate([
    //         'source_id' => ['required', 'exists:sources,id'],
    //     ]);

    //     $assignment->update([
    //         'source_id' => $data['source_id'],
    //         'status'    => 'assigned', // برگشت به حالت ارجاع‌شده
    //     ]);

    //     return back()->with('success', 'منبع این آیتم تغییر کرد.');
    // }

    // ثبت نهایی خرید برای یک آیتم
    public function finalizeItem(Request $request, PreInvoiceItem $item)
    {
        $preInvoice = $item->preInvoice;
        // abort_unless($preInvoice && $preInvoice->direction === 'purchase', 404);

        $data = $request->validate([
            'final_quantity'    => ['required', 'numeric', 'min:0.001'],
            'final_unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($item, $data) {
            $finalTotal = $data['final_quantity'] * $data['final_unit_price'];

            $item->final_quantity    = $data['final_quantity'];
            $item->final_unit_price  = $data['final_unit_price'];
            $item->final_total_price = $finalTotal;
            $item->status            = 'purchased'; // اگر ستون status داری
            $item->save();
        });

        return back()->with('success', 'خرید آیتم با اطلاعات نهایی ثبت شد.');
    }

    // تایید خرید کل این پیش‌فاکتور خرید توسط مدیر خرید
    public function approvePurchase(PreInvoice $purchasePreInvoice)
    {
        // abort_unless($purchasePreInvoice->direction === 'purchase', 404);

        $purchasePreInvoice->load(['purchaseItems', 'salePreInvoice']);

        // باید همه آیتم‌ها نهایی شده باشند
        $notFinalized = $purchasePreInvoice->purchaseItems
            ->filter(fn($item) => !$item->final_quantity || !$item->final_unit_price);

        if ($notFinalized->count() > 0) {
            return back()->with('error', 'همه آیتم‌ها هنوز نهایی نشده‌اند.');
        }

        DB::transaction(function () use ($purchasePreInvoice) {
            // این پیش‌فاکتور خرید را علامت‌گذاری کن
            $purchasePreInvoice->update([
                'status' => PreInvoiceStatus::PurchaseCompleted->value,
            ]);

            // اگر همه PurchasePreInvoiceهای مرتبط با این فروش نهایی شده‌اند،
            // وضعیت پیش‌فاکتور فروش را هم آپدیت کن
            $sale = $purchasePreInvoice->salePreInvoice;

            if ($sale) {
                $allDone = $sale->purchasePreInvoices()
                    ->where('status', '!=', PreInvoiceStatus::PurchaseCompleted->value)
                    ->doesntExist();

                if ($allDone) {
                    $sale->status = PreInvoiceStatus::PurchaseCompleted;
                    $sale->save();
                }
            }
        });

        return back()->with('success', 'خرید این پیش‌فاکتور تایید و به فروش منتقل شد.');
    }

    public function approveSupplierPayment(Request $request, PreInvoices $preInvoice)
    {
        $this->authorize('approveSupplierPayment', $preInvoice);

        abort_unless($preInvoice->direction === 'purchase', 403);

        // اینجا می‌توانی چک کنی که همه payment plan ها یا پرداخت‌های لازم ثبت شده‌اند
        // و حداقل یک پرداخت تایید شده است.

        $preInvoice->supplier_payment_approved = true;
        $preInvoice->save();

        return redirect()
            ->route('purchase_pre_invoices.purchase_show', $preInvoice->id)
            ->with('success', 'پرداخت به منبع تایید شد. حالا کارشناس خرید می‌تواند وزن نهایی را ثبت کند.');
    }

      public function finalizeItemPurchase(Request $request, PreInvoiceItem $item)
    {
        $preInvoice = $item->preInvoice;

        // abort_unless($preInvoice && $preInvoice->direction === 'purchase', 403);
        // abort_unless($preInvoice->supplier_payment_approved, 403);

        // $this->authorize('finalizeItemPurchase', $item);

        $data = $request->validate([
            'final_purchase_weight' => ['required', 'numeric', 'min:0.001'],
        ]);

        $item->final_purchase_weight = $data['final_purchase_weight'];
        $item->purchase_status       = 'purchased';
        $item->save();

        // اگر همه آیتم‌های این پیش‌فاکتور خرید شده باشند، وضعیت pre_invoice خرید را هم به purchased ببریم
        if ($preInvoice->items()->where('purchase_status', '!=', 'purchased')->count() === 0) {
            $preInvoice->purchase_status = 'purchased';
            $preInvoice->save();
        }

        return back()->with('success', 'خرید این آیتم نهایی شد.');
    }

}

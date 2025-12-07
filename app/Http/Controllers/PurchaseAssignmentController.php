<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\PreInvoiceItem;
use App\Models\PurchaseAssignment;
use App\Enums\PreInvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PurchaseAssignmentController extends Controller
{
    public function store(Request $request)
    {
        // فقط مدیر خرید مجاز است
        // $this->authorize('assignPurchase', PreInvoice::class); // بعداً در پالیسی اضافه می‌شود

        $data = $request->validate([
            'pre_invoice_item_id' => 'required|exists:pre_invoice_items,id',
            'buyer_id'            => 'required|exists:users,id',
            'source_id'           => 'nullable|exists:sources,id',
        ]);

        $item = PreInvoiceItem::with('preInvoice')->findOrFail($data['pre_invoice_item_id']);

        PurchaseAssignment::create([
            'pre_invoice_item_id' => $item->id,
            'buyer_id'            => $data['buyer_id'],
            'source_id'           => $data['source_id'] ?? null,
            'status'              => 'assigned',
            'created_by'          => auth()->id(),
        ]);

        return back()->with('success','آیتم به کارشناس خرید ارجاع شد.');
    }

    protected function cannotChange(PurchaseAssignment $assignment): bool
    {
        $item = $assignment->item;
        $purchasePre = $item?->purchasePreInvoice;

        
        // اگر قیمت‌گذاری نهایی/وزن نهایی وارد شده، دیگر اجازه تغییر نیست
        if ($item && ($item->final_quantity || $item->final_unit_price)) {
            return true;
        }
        // dd($purchasePre->status);

        // اگر خود پیش‌فاکتور خرید از فاز draft/در حال استعلام خارج شده (مثلاً در انتظار مالی یا بعدش)
        if ($purchasePre && in_array($purchasePre->status, [
            PreInvoiceStatus::WaitingFinancePurchase,
            // PreInvoiceStatus::FinancePurchaseApproved,
            // PreInvoiceStatus::FinancePurchaseRejected,
            PreInvoiceStatus::PurchaseCompleted,
        ], true)) {
            return true;
        }

        return false;
    }

    public function changeBuyer(Request $request, PurchaseAssignment $assignment)
    {
        // $item = $assignment->item;
        // $salePre = $item?->preInvoice?->salePreInvoice; // اگر آیتم روی purchase_pre_invoice است، به فروش لینک شود

        $item = $assignment->item;

        // آیتم باید به یک پیش‌فاکتور خرید وصل باشد
        $purchasePre = $item?->purchasePreInvoice;

        // از پیش‌فاکتور خرید برس به پیش‌فاکتور فروش
        $salePre = $purchasePre?->salePreInvoice;

        if (! $item || ! $salePre) {
            return back()->with('error', 'ساختار آیتم/پیش‌فاکتور معتبر نیست.');
        }

        if ($this->cannotChange($assignment)) {
            return back()->with('error', 'بعد از قیمت‌گذاری/خرید نهایی امکان تغییر کارشناس وجود ندارد.');
        }

        $data = $request->validate([
            'buyer_id' => ['required','exists:users,id'],
        ]);

        $newBuyerId = (int) $data['buyer_id'];

        // اگر همان خریدار قبلی است، کاری نکن
        if ($assignment->buyer_id === $newBuyerId) {
            return back()->with('success', 'کارشناس خرید قبلاً روی همین شخص تنظیم شده است.');
        }

        DB::transaction(function () use ($assignment, $item, $salePre, $newBuyerId) {
            $oldPurchasePreId = $item->purchase_pre_invoice_id;
            $oldBuyerId       = $assignment->buyer_id;
            $sourceId         = $assignment->source_id;

            // ۱) خود assignment را آپدیت کن
            $assignment->buyer_id = $newBuyerId;
            $assignment->status   = 'assigned'; // یا reset به مرحله مناسب
            $assignment->save();

            // ۲) پیدا کردن یا ساختن پیش‌فاکتور خرید جدید برای (فروش + منبع + کارشناس جدید)
            $newPurchasePreInvoice = PreInvoice::firstOrCreate(
                [
                    'direction'           => 'purchase',
                    'sale_pre_invoice_id' => $salePre->id,
                    'source_id'           => $sourceId,
                    'buyer_id'            => $newBuyerId,
                ],
                [
                    'type'         => $salePre->type,
                    'status'       => PreInvoiceStatus::Draft,
                    'customer_id'  => $salePre->customer_id,
                    'total_amount' => 0,
                    'formal_extra' => null,
                    'created_by'   => auth()->id(),
                ]
            );

            // ۳) آیتم را از پیش‌فاکتور قبلی جدا و به این پیش‌فاکتور جدید متصل کن
            $item->purchase_pre_invoice_id = $newPurchasePreInvoice->id;
            $item->save();

            // ۴) جمع مبلغ پیش‌فاکتور جدید را به‌روزرسانی کن
            $newSum = $newPurchasePreInvoice->purchaseItems()
                ->sum(DB::raw('quantity * purchase_unit_price'));
            $newPurchasePreInvoice->update(['total_amount' => $newSum]);

            // ۵) اگر پیش‌فاکتور خرید قبلی دیگر آیتمی ندارد، حذفش کن؛ وگرنه total_amount‌اش را آپدیت کن
            if ($oldPurchasePreId && $oldPurchasePreId !== $newPurchasePreInvoice->id) {
                $old = PreInvoice::find($oldPurchasePreId);

                if ($old) {
                    $oldItemsCount = $old->purchaseItems()->count();

                    if ($oldItemsCount === 0) {
                        $old->delete();
                    } else {
                        $oldSum = $old->purchaseItems()
                            ->sum(DB::raw('quantity * purchase_unit_price'));
                        $old->update(['total_amount' => $oldSum]);
                    }
                }
            }
        });

        return back()->with('success', 'کارشناس خرید این آیتم تغییر و پیش‌فاکتورهای خرید به‌روزرسانی شدند.');
    }

    public function changeSource(Request $request, PurchaseAssignment $assignment)
    {
        // $item = $assignment->item;
        // $salePre = $item?->preInvoice?->salePreInvoice;

        $item = $assignment->item;

        // آیتم باید به یک پیش‌فاکتور خرید وصل باشد
        $purchasePre = $item?->purchasePreInvoice;

        // از پیش‌فاکتور خرید برس به پیش‌فاکتور فروش
        $salePre = $purchasePre?->salePreInvoice;

        // dd($purchasePre, $salePre);

        // dd($salePre);

        if (! $item || ! $salePre) {
            return back()->with('error', 'ساختار آیتم/پیش‌فاکتور معتبر نیست.');
        }

        

        if ($this->cannotChange($assignment)) {
            return back()->with('error', 'بعد از قیمت‌گذاری/خرید نهایی امکان تغییر منبع وجود ندارد.');
        }

        // dd($salePre);

        $data = $request->validate([
            'source_id' => ['required','exists:sources,id'],
        ]);

        $newSourceId = (int) $data['source_id'];

        if ($assignment->source_id === $newSourceId) {
            return back()->with('success', 'منبع این آیتم قبلاً روی همین منبع تنظیم شده است.');
        }

        

        DB::transaction(function () use ($assignment, $item, $salePre, $newSourceId) {
            $oldPurchasePreId = $item->purchase_pre_invoice_id;
            $buyerId          = $assignment->buyer_id;
            $oldSourceId      = $assignment->source_id;

            // ۱) خود assignment را آپدیت کن
            $assignment->source_id = $newSourceId;
            $assignment->status    = 'assigned';
            $assignment->save();

            // ۲) پیدا کردن یا ساختن پیش‌فاکتور خرید جدید برای (فروش + کارشناس + منبع جدید)
            $newPurchasePreInvoice = PreInvoice::firstOrCreate(
                [
                    'direction'           => 'purchase',
                    'sale_pre_invoice_id' => $salePre->id,
                    'source_id'           => $newSourceId,
                    'buyer_id'            => $buyerId,
                ],
                [
                    'type'         => $salePre->type,
                    'status'       => PreInvoiceStatus::Draft,
                    'customer_id'  => $salePre->customer_id,
                    'total_amount' => 0,
                    'formal_extra' => null,
                    'created_by'   => auth()->id(),
                ]
            );

            // ۳) آیتم به پیش‌فاکتور خرید جدید منتقل شود
            $item->purchase_pre_invoice_id = $newPurchasePreInvoice->id;
            $item->save();

            // ۴) جمع جدید
            $newSum = $newPurchasePreInvoice->purchaseItems()
                ->sum(DB::raw('quantity * purchase_unit_price'));
            $newPurchasePreInvoice->update(['total_amount' => $newSum]);

            // ۵) پیش‌فاکتور قبلی اگر خالی شد حذف شود، وگرنه total_amount اصلاح شود
            if ($oldPurchasePreId && $oldPurchasePreId !== $newPurchasePreInvoice->id) {
                $old = PreInvoice::find($oldPurchasePreId);

                if ($old) {
                    $oldItemsCount = $old->purchaseItems()->count();

                    if ($oldItemsCount === 0) {
                        $old->delete();
                    } else {
                        $oldSum = $old->purchaseItems()
                            ->sum(DB::raw('quantity * purchase_unit_price'));
                        $old->update(['total_amount' => $oldSum]);
                    }
                }
            }
        });

        return back()->with('success', 'منبع این آیتم تغییر و پیش‌فاکتورهای خرید به‌روزرسانی شدند.');
    }
}

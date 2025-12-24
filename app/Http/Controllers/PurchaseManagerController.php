<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\PreInvoiceItem;
use App\Models\PurchaseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PurchaseManagerController extends Controller
{
    public function review(PreInvoice $pre_invoice)
    {
        $user = auth()->user();
        abort_unless($user->isManagement(), 403); // همان متد کمکی که قبلاً گذاشتیم

        // فقط پیش‌فاکتور فروش
        abort_unless($pre_invoice->direction === 'sale', 404);

        $preInvoice = $pre_invoice->load([
            'items.product',
            'items.purchaseAssignments.buyer',
            'items.purchaseAssignments.source',
            'items.chosenPurchaseAssignment',
        ]);

        return view('purchase_manager.pre_invoices.review', compact('preInvoice'));
    }

    public function approvePurchasePreInvoice(PreInvoice $preInvoice)
    {
        abort_unless($preInvoice->direction === 'purchase', 404);

        // $this->authorize('approve-purchase-pre-invoice', $preInvoice);

        // تایید مدیر خرید → ارسال به مالی
        $preInvoice->status = \App\Enums\PreInvoiceStatus::WaitingFinancePurchase;
        $preInvoice->save();

        return back()->with('success','این پیش‌فاکتور خرید توسط مدیر خرید تایید و به مالی ارجاع شد.');
    }

    public function rejectPurchasePreInvoice(Request $request, PreInvoice $preInvoice)
    {
        abort_unless($preInvoice->direction === 'purchase', 404);

        $data = $request->validate([
            'purchase_manager_reject_reason' => ['required','string','max:5000'],
        ]);

        $preInvoice->status = \App\Enums\PreInvoiceStatus::Rejected;
        $preInvoice->purchase_manager_reject_reason = $data['purchase_manager_reject_reason'] ?? null;
        $preInvoice->save();

        return back()->with('success','این پیش‌فاکتور خرید توسط مدیر خرید رد شد.');
    }


    public function choosePrices(Request $request, PreInvoice $preInvoice)
    {
        $choices = $request->input('choices', []);

        DB::transaction(function () use ($choices, $preInvoice) {

            foreach ($preInvoice->items as $item) {

                if (! isset($choices[$item->id])) {
                    // آیتمی که انتخابی براش نیومده، فعلاً skip می‌شود
                    continue;
                }

                $assignmentId = $choices[$item->id];
                $assignment   = $item->purchaseAssignments()->find($assignmentId);

                if (! $assignment) {
                    continue;
                }

                $oldPurchasePreId = $item->purchase_pre_invoice_id;

                $item->chosen_purchase_assignment_id = $assignment->id;
                $item->purchase_unit_price           = $assignment->unit_price;
                $item->save();

                $purchasePreInvoice = PreInvoice::firstOrCreate(
                    [
                        'direction'           => 'purchase',
                        'sale_pre_invoice_id' => $preInvoice->id,
                        'source_id'           => $assignment->source_id,
                        'buyer_id'            => $assignment->buyer_id,
                    ],
                    [
                        'type'         => $preInvoice->type,
                        'status'       => \App\Enums\PreInvoiceStatus::Draft,
                        'customer_id'  => $preInvoice->customer_id,
                        'total_amount' => 0,
                        'formal_extra' => null,
                        'created_by'   => auth()->id(),
                    ]
                );

                $item->purchase_pre_invoice_id = $purchasePreInvoice->id;
                $item->save();

                $newSum = $purchasePreInvoice->items()
                    ->sum(DB::raw('quantity * purchase_unit_price'));
                $purchasePreInvoice->update(['total_amount' => $newSum]);

                if ($oldPurchasePreId && $oldPurchasePreId != $purchasePreInvoice->id) {
                    $old = PreInvoice::find($oldPurchasePreId);
                    if ($old) {
                        $oldSum = $old->purchaseItems()->sum(DB::raw('quantity * purchase_unit_price'));

                        if ($oldSum == 0 && $old->purchaseItems()->count() == 0) {
                            $old->delete();
                        } else {
                            $old->update(['total_amount' => $oldSum]);
                        }
                    }
                }
            }

            // 🔹 بعد از پردازش، چک کنیم آیا همه آیتم‌ها assignment انتخاب‌شده دارند یا نه
            $preInvoice->load('items');

            $allAssigned = $preInvoice->items->every(function ($item) {
                return ! is_null($item->chosen_purchase_assignment_id);
            });

            if ($allAssigned) {
                $preInvoice->status = \App\Enums\PreInvoiceStatus::ApprovedManager;
                $preInvoice->save();
            }


            // این status روی آخرین purchasePreInvoice ساخته‌شده ست می‌شود.
            // اگر چند purchasePreInvoice مختلف داری، این‌جا بهتر است همه‌شان را آپدیت کنی.
            $purchasePreInvoice->status = \App\Enums\PreInvoiceStatus::WaitingFinancePurchase;
            $purchasePreInvoice->save();
        });

        return back()->with('success','قیمت‌های نهایی خرید ثبت و پیش‌فاکتورهای خرید بر اساس آخرین انتخاب‌ها به‌روزرسانی شدند.');
    }

    
    // public function choosePrices(Request $request, PreInvoice $preInvoice)
    // {
    //     $choices = $request->input('choices', []);

    //     DB::transaction(function () use ($choices, $preInvoice) {
            
    //         foreach ($preInvoice->items as $item) {

    //             if (! isset($choices[$item->id])) {
    //                 continue;
    //             }

    //             $assignmentId = $choices[$item->id];
    //             $assignment   = $item->purchaseAssignments()->find($assignmentId);

    //             if (! $assignment) {
    //                 continue;
    //             }

    //             $oldPurchasePreId = $item->purchase_pre_invoice_id;

    //             $item->chosen_purchase_assignment_id = $assignment->id;
    //             $item->purchase_unit_price           = $assignment->unit_price;
    //             $item->save();

    //             $purchasePreInvoice = PreInvoice::firstOrCreate(
    //                 [
    //                     'direction'           => 'purchase',
    //                     'sale_pre_invoice_id' => $preInvoice->id,
    //                     'source_id'           => $assignment->source_id,
    //                     'buyer_id'            => $assignment->buyer_id,
    //                 ],
    //                 [
    //                     'type'         => $preInvoice->type,
    //                     'status'       => \App\Enums\PreInvoiceStatus::Draft,
    //                     'customer_id'  => $preInvoice->customer_id,
    //                     'total_amount' => 0,
    //                     'formal_extra' => null,
    //                     'created_by'   => auth()->id(),
    //                 ]
    //             );
    //             // $purchasePreInvoice = PreInvoice::firstOrCreate(
    //             //     [
    //             //         'direction'           => 'purchase',
    //             //         'sale_pre_invoice_id' => $preInvoice->id,
    //             //         'source_id'           => $assignment->source_id,
    //             //         'buyer_id'            => $assignment->buyer_id,
    //             //     ],
    //             //     [
    //             //         'type'         => $preInvoice->type,
    //             //         'status'       => \App\Enums\PreInvoiceStatus::Draft,
    //             //         'customer_id'  => $preInvoice->customer_id,
    //             //         'total_amount' => 0,
    //             //         'formal_extra' => null,
    //             //         'created_by'   => auth()->id(),
    //             //     ]
    //             // );

    //             $item->purchase_pre_invoice_id = $purchasePreInvoice->id;
    //             $item->save();

    //             $newSum = $purchasePreInvoice->items()
    //                 ->sum(DB::raw('quantity * purchase_unit_price'));
    //             $purchasePreInvoice->update(['total_amount' => $newSum]);

    //             if ($oldPurchasePreId && $oldPurchasePreId != $purchasePreInvoice->id) {
    //                 $old = PreInvoice::find($oldPurchasePreId);
    //                 if ($old) {
    //                     $oldSum = $old->purchaseItems()->sum(DB::raw('quantity * purchase_unit_price'));

    //                     if ($oldSum == 0 && $old->purchaseItems()->count() == 0) {
    //                         // اگر هیچ آیتمی نمانده، خود پیش‌فاکتور خرید را حذف کن
    //                         $old->delete();
    //                     } else {
    //                         $old->update(['total_amount' => $oldSum]);
    //                     }
    //                 }
    //             }


    //             // if ($oldPurchasePreId && $oldPurchasePreId != $purchasePreInvoice->id) {
    //             //     $old = PreInvoice::find($oldPurchasePreId);
    //             //     if ($old) {
    //             //         $oldSum = $old->items()
    //             //             ->sum(DB::raw('quantity * purchase_unit_price'));
    //             //         $old->update(['total_amount' => $oldSum]);
    //             //     }
    //             // }
    //         }

    //         $preInvoice->status = \App\Enums\PreInvoiceStatus::ApprovedManager;
    //         $preInvoice->save();

    //         $purchasePreInvoice->status = \App\Enums\PreInvoiceStatus::WaitingFinancePurchase;
    //         $purchasePreInvoice->save();

    //     });


    //     return back()->with('success','قیمت‌های نهایی خرید ثبت و پیش‌فاکتورهای خرید بر اساس آخرین انتخاب‌ها به‌روزرسانی شدند.');
    // }


    
    public function chooseSupplierForItem(PurchaseAssignment $assignment)
    {
        $item = $assignment->item;              // رابطه item() روی PurchaseAssignment
        $salePreInvoice = $item->salePreInvoice; // رابطه salePreInvoice() روی PreInvoiceItem

        // فقط روی پیش‌فاکتورهای فروش مجاز است
        // $this->authorize('chooseSupplier', $salePreInvoice);

        return DB::transaction(function () use ($assignment, $item, $salePreInvoice) {

            $oldPurchasePreId = $item->purchase_pre_invoice_id;

            // به‌روزرسانی آیتم با تامین‌کننده/قیمت جدید
            $item->chosen_purchase_assignment_id = $assignment->id;
            $item->purchase_unit_price           = $assignment->unit_price;
            $item->source_id                     = $assignment->source_id;    // اگر این ستون را داری
            $item->buyer_id                      = $assignment->buyer_id ?? $item->buyer_id;
            $item->save();

            // پیدا/ایجاد پیش‌فاکتور خرید برای این منبع و کارشناس خرید
            $purchasePreInvoice = PreInvoice::firstOrCreate(
                [
                    'direction'           => 'purchase',
                    'sale_pre_invoice_id' => $preInvoice->id,
                    'source_id'           => $assignment->source_id,
                    'buyer_id'            => $assignment->buyer_id,
                ],
                [
                    'type'         => $salePreInvoice->type,
                    'status'       => 'waiting_finance', // یا هر status مناسب تو
                    'total_amount' => 0,
                    'formal_extra' => null,
                    'created_by'   => auth()->id(),
                ]
            );

            // اتصال آیتم به پیش‌فاکتور خرید جدید
            $item->purchase_pre_invoice_id = $purchasePreInvoice->id;
            $item->save();

            // به‌روزرسانی جمع پیش‌فاکتور خرید جدید
            $newSum = $purchasePreInvoice->items()
                ->sum(DB::raw('quantity * purchase_unit_price'));
            $purchasePreInvoice->update(['total_amount' => $newSum]);

            // اگر قبلاً در پیش‌فاکتور خرید دیگری بوده، جمع آن را هم اصلاح کن
            if ($oldPurchasePreId && $oldPurchasePreId != $purchasePreInvoice->id) {
                $old = PreInvoice::find($oldPurchasePreId);
                if ($old) {
                    $oldSum = $old->items()
                        ->sum(DB::raw('quantity * purchase_unit_price'));
                    $old->update(['total_amount' => $oldSum]);
                }
            }

            return back()->with('success', 'تامین‌کننده برای این آیتم به‌روزرسانی شد.');
        });
    }

    public function reassignToNextSupplier(PreInvoiceItem $item)
    {
        return DB::transaction(function () use ($item) {

            $current = $item->chosenAssignment; // ممکن است null باشد

            // اگر فعلاً چیزی انتخاب نشده، از کمترین id شروع می‌کنیم
            $currentId = $current?->id ?? 0;

            // پیدا کردن assignment بعدی برای همین آیتم، بر اساس id
            $next = $item->assignments()
                ->where('id', '>', $currentId)
                ->orderBy('id')
                ->first();

            if (! $next) {
                return back()->with('error', 'پیشنهاد دیگری برای این آیتم وجود ندارد.');
            }

            // آپدیت آیتم با پیشنهاد جدید
            $item->chosen_purchase_assignment_id = $next->id;
            $item->purchase_unit_price          = $next->unit_price;
            $item->source_id                    = $next->source_id ?? $item->source_id;
            $item->buyer_id                     = $next->buyer_id ?? $item->buyer_id;
            $item->save();

            // مدیریت پیش‌فاکتور خرید جدید/قدیمی
            $oldPurchasePreId = $item->purchase_pre_invoice_id;

            $salePreInvoice = $item->salePreInvoice;

            $purchasePreInvoice = PreInvoice::firstOrCreate(
                [
                    'direction'           => 'purchase',
                    'sale_pre_invoice_id' => $salePreInvoice->id,
                    'source_id'           => $next->source_id,
                    'buyer_id'            => $next->buyer_id,
                ],
                [
                    'type'         => $salePreInvoice->type,
                    'status'       => 'waiting_finance',
                    'total_amount' => 0,
                    'formal_extra' => null,
                    'created_by'   => auth()->id(),
                ]
            );

            $item->purchase_pre_invoice_id = $purchasePreInvoice->id;
            $item->save();

            // جمع جدید این پیش‌فاکتور خرید
            $newSum = $purchasePreInvoice->items()
                ->sum(DB::raw('quantity * purchase_unit_price'));
            $purchasePreInvoice->update(['total_amount' => $newSum]);

            // اگر قبلاً در purchase_pre_invoice دیگری بوده، جمع آن را هم اصلاح کن
            if ($oldPurchasePreId && $oldPurchasePreId != $purchasePreInvoice->id) {
                $old = PreInvoice::find($oldPurchasePreId);
                if ($old) {
                    $oldSum = $old->items()
                        ->sum(DB::raw('quantity * purchase_unit_price'));
                    $old->update(['total_amount' => $oldSum]);
                }
            }

            return back()->with('success', 'آیتم به پیشنهاد بعدی ارجاع شد و پیش‌فاکتور خرید به‌روزرسانی شد.');
        });
    }

    // public function choosePrices(Request $request, PreInvoice $pre_invoice)
    // {
    //     $user = auth()->user();
    //     // abort_unless($user->isManagement(), 403);
    //     // abort_unless($pre_invoice->direction === 'sale', 404);

        
    //     $data = $request->validate([
    //         // آرایه‌ای: item_id => assignment_id
    //         'choices'   => 'required|array',
    //         'choices.*' => 'nullable|exists:purchase_assignments,id',
    //     ]);

    //     foreach ($pre_invoice->items as $item) {
    //         $assignmentId = $data['choices'][$item->id] ?? null;

    //         if ($assignmentId) {
    //             $assignment = PurchaseAssignment::where('id', $assignmentId)
    //                 ->where('pre_invoice_item_id', $item->id)
    //                 ->first();

    //             if ($assignment && $assignment->unit_price !== null) {
    //                 $item->chosen_purchase_assignment_id = $assignment->id;
    //                 $item->purchase_unit_price           = $assignment->unit_price;
    //                 $item->save();
    //             }
    //         } else {
    //             // اگر چیزی انتخاب نشده، مقدار قبلی را پاک نکنیم (یا طبق نیاز تو تنظیم کن)
    //         }
    //     }

    //     // می‌توانیم وضعیت پیش‌فاکتور را هم آپدیت کنیم:
    //     $pre_invoice->status = 'priced_by_purchase';
    //     $pre_invoice->save();

    //     return redirect()
    //         ->route('purchase-manager.pre-invoices.review', $pre_invoice->id)
    //         ->with('success','قیمت‌های خرید نهایی برای آیتم‌ها ثبت شد.');
    // }

    public function readyForSales()
    {
        // $user = auth()->user();
        // abort_unless($user->isManagement(), 403);
        //  dd('readyForSales reached');
        $preInvoices = PreInvoice::sales()
            ->where('status', 'priced_by_purchase')
            ->whereHas('items', function($q){
                $q->whereNotNull('purchase_unit_price');
            })
            ->with('customer')
            ->orderByDesc('id')
            ->paginate(30);

        return view('purchase_manager.pre_invoices.ready_for_sales', compact('preInvoices'));
    }


}

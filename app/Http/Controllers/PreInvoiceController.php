<?php

namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Enums\PreInvoiceStatus;
use App\Models\Customer;
use App\Models\Source;
use App\Models\User;
use App\Models\PreInvoiceItem;
use Illuminate\Http\Request;


use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;



class PreInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // public function index()
    // {
    //     // $this->authorize('viewAny', PreInvoice::class); // اگر خواستی متد viewAny هم به پالیسی اضافه می‌کنیم

    //     $preInvoices = PreInvoice::with('customer','source')
    //         ->orderByDesc('id')
    //         ->paginate(20);

    //     return view('pre_invoices.index', compact('preInvoices'));
    // }

    public function index()
    {
        $preInvoices = PreInvoice::with(['customer.companies'])
            ->where('direction', 'sale')
            ->notInvoiced()
            ->latest('id')   // یا فقط latest() کافی است
            ->paginate(20);

        return view('pre_invoices.index', compact('preInvoices'));
    }



    // public function show(PreInvoice $pre_invoice)
    // {
    //     $pre_invoice->load('customer','items.product','source');
    //     return view('pre_invoices.show', compact('pre_invoice'));
    // }

    // public function show(PreInvoice $pre_invoice)
    // {
    //     // فروش
    //     // abort_unless($pre_invoice->direction === 'sale', 404);

    //     $pre_invoice->load(['customer.company','items.product','source']);

    //     return view('pre_invoices.show', compact('pre_invoice'));
    // }

    // public function show(PreInvoice $pre_invoice)
    // {
    //     // abort_unless($pre_invoice->direction === 'sale', 404);

    //     $pre_invoice->load([
    //         'customer',
    //         'saleItems.product',
    //         'purchasePreInvoices.source',
    //         'purchasePreInvoices.buyer',
    //     ]);

    //     return view('pre_invoices.show', compact('pre_invoice'));
    // }

    
    public function show(PreInvoice $pre_invoice)
    {
        abort_unless($pre_invoice->direction === 'sale', 404);

        // $pre_invoice->load(['customer','saleItems.product']);
        // $pre_invoice->load([
        //     'customer',
        //     'saleItems.product',
        //     'purchasePreInvoices.source',
        //     'purchasePreInvoices.buyer',
        // ]);

        // $preInvoice = PreInvoice::with(['paymentPlans', 'payments', 'customer', 'items.product'])->findOrFail($id);


        $pre_invoice->load([
            'customer',
            'saleItems.product',
            'saleItems.purchaseAssignments.buyer',
            'purchasePreInvoices.source',
            'purchasePreInvoices.buyer',
            'plans',
            'payments',
            'paymentPlans',
            'purchasePreInvoices.items',
            'purchasePreInvoices.purchaseItems',
        ]);

         // محاسبه جمع‌ها (سود و ... روی آیتم‌ها)
        $items = $pre_invoice->saleItems;

        $totals = [
            'quantity'        => $items->sum('quantity'),
            'purchase_amount' => $items->sum(function ($item) {
                return $item->quantity * ($item->purchase_unit_price ?? 0);
            }),
            'sale_amount'     => $items->sum(function ($item) {
                return $item->quantity * ($item->sale_unit_price ?? $item->unit_price ?? 0);
            }),
            'profit_amount'   => 0,
            'profit_percent'  => 0,
        ];

        $totals['profit_amount'] = $totals['sale_amount'] - $totals['purchase_amount'];

        if ($totals['purchase_amount'] > 0) {
            $totals['profit_percent'] = ($totals['profit_amount'] / $totals['purchase_amount']) * 100;
        }

        // محاسبه پرداخت‌ها روی پیش‌فاکتور
        $totalPaid = $pre_invoice->payments()
            ->where('status', 'confirmed')
            ->sum('paid_amount'); // یا sum('amount') اگر منطق‌ات این است

        $remaining = $pre_invoice->total_amount - $totalPaid;


        // return view('pre_invoices.show', compact('pre_invoice','totals'));
        
        return view('pre_invoices.show', compact('pre_invoice','totals','totalPaid','remaining'));
    }



    public function purchaseWaitingFinance()
    {
        $preInvoices = PreInvoice::where('direction', 'purchase')
            ->where('status', PreInvoiceStatus::WaitingFinancePurchase)
            ->with(['source','buyer','salePreInvoice'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('finance.pre_invoices.purchase_pre_invoices.index', compact('preInvoices'));
    }

    public function financeApprovePurchase(PreInvoice $preInvoice)
    {
        abort_unless($preInvoice->direction === 'purchase', 404);

        // اجازه فقط برای نقش مالی/مدیر
        // $this->authorize('finance-approve-purchase', $preInvoice);

        $preInvoice->status = PreInvoiceStatus::FinancePurchaseApproved;
        $preInvoice->finance_reject_reason = null;
        $preInvoice->save();

        // اینجا می‌توانی روی PreInvoice فروش اصلی هم یک فلگ بگذاری
        // مثلاً اگر همه PurchasePreInvoiceهای وابسته به این فروش تایید مالی شدند،
        // وضعیت PreInvoice فروش را بفرستی به مرحله بعد (برای فروش).
        // این را می‌توانیم در قدم بعدی دقیق کنیم.

        return back()->with('success','پیش‌فاکتور خرید توسط مالی تایید شد.');
    }

    public function financeRejectPurchase(Request $request, PreInvoice $preInvoice)
    {
        // abort_unless($preInvoice->direction === 'purchase', 404);

        // $this->authorize('finance-reject-purchase', $preInvoice);

        $data = $request->validate([
            'finance_reject_reason' => ['required','string','max:5000'],
        ]);

        $preInvoice->status = PreInvoiceStatus::FinancePurchaseRejected;
        $preInvoice->finance_reject_reason = $data['finance_reject_reason'];
        $preInvoice->save();

        return back()->with('success','پیش‌فاکتور خرید توسط مالی رد شد.');
    }



    // public function showPurchase(PreInvoice $preInvoice)
    // {
    //     abort_unless($preInvoice->direction === 'purchase', 404);

    //     $preInvoice->load(['source','buyer','purchaseItems.product','salePreInvoice']);

    //     return view('pre_invoices.purchase_show', compact('preInvoice'));
    // }

    
    public function showPurchase(PreInvoice $preInvoice)
    {
        abort_unless($preInvoice->direction === 'purchase', 404);

        $preInvoice->load([
            'source',
            'buyer',
            'purchaseItems.product',
            'salePreInvoice',
            'paymentPlans', // پلن‌های پرداخت به منبع
        ]);
        

        // dd($preInvoice->purchaseItems->toArray());

        // لیست منابع ممکن برای تغییر منبع
        $sources = Source::orderBy('id')->get();

        // لیست کاربران که می‌توانند کارشناس خرید باشند
        $buyers = User::whereHas('roles', function ($q) {
            $q->whereIn('roles.name', ['purchase_expert', 'purchase_manager']);
        })->orderBy('users.id')->get();

        // dd($buyers->toArray());

        return view('pre_invoices.purchase_show', compact('preInvoice', 'sources', 'buyers'));
    }



    // public function showPurchase(PreInvoice $preInvoice)
    // {
    //     // خرید
    //     abort_unless($preInvoice->direction === 'purchase', 404);

    //     $preInvoice->load(['source','buyer','items.product','salePreInvoice']);

    //     return view('pre_invoices.purchase_show', compact('preInvoice'));
    // }

    public function editSalePrices(PreInvoice $pre_invoice)
    {
        // if (! $pre_invoice->canBePricedBySales()) {
        //     abort(403,'این پیش‌فاکتور در وضعیت مناسب برای قیمت‌گذاری فروش نیست.');
        // }

        $pre_invoice->load('items.product','customer','source');

        return view('pre_invoices.edit_sales_prices', compact('pre_invoice'));
    }


    public function print(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->isReadyForCustomer()) {
            abort(403, 'این پیش‌فاکتور هنوز برای نمایش/چاپ به مشتری آماده نیست.');
        }

        $pre_invoice->load('customer','items.product','source');

        return view('pre_invoices.print', compact('pre_invoice'));
    }


    // 4 → 5
    public function sendToPurchase(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canSendToPurchase()) {
            return back()->with('error','این پیش‌فاکتور در وضعیت مناسب برای ارسال به خرید نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::WaitingPurchase,
        ]);

        return back()->with('success','پیش‌فاکتور به واحد خرید ارسال شد.');
    }

    // 5 → priced_by_purchase
    public function priceByPurchase(Request $request, PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canBePricedByPurchase()) {
            return back()->with('error','وضعیت فعلی اجازه قیمت‌گذاری خرید را نمی‌دهد.');
        }

        // TODO: ذخیره قیمت‌های خرید روی آیتم‌ها

        $pre_invoice->update([
            'status' => PreInvoiceStatus::PricedByPurchase,
        ]);

        return back()->with('success','قیمت‌گذاری خرید ثبت شد.');
    }

    // priced_by_purchase → approved_manager
    public function approvePurchase(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canApproveByPurchaseManager()) {
            return back()->with('error','این پیش‌فاکتور هنوز برای تایید مدیر خرید آماده نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::ApprovedManager,
        ]);

        return back()->with('success','تایید مدیر خرید ثبت شد و پیش‌فاکتور به فروش ارجاع شد.');
    }

    // approved_manager → priced_by_sales
    public function priceBySales(Request $request, PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canBePricedBySales()) {
            return back()->with('error','این پیش‌فاکتور برای قیمت‌گذاری فروش آماده نیست.');
        }

        // TODO: ذخیره قیمت‌های فروش، حاشیه سود و ...

        $pre_invoice->update([
            'status' => PreInvoiceStatus::PricedBySales,
        ]);

        return back()->with('success','قیمت‌گذاری فروش ثبت شد.');
    }

    // priced_by_sales → waiting_sales_approval
    public function sendToSalesApproval(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canSendToSalesApproval()) {
            return back()->with('error','این پیش‌فاکتور در وضعیت مناسب برای ارسال به مدیر فروش نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::WaitingSalesApproval,
        ]);

        return back()->with('success','پیش‌فاکتور برای تایید مدیر فروش ارسال شد.');
    }

    // waiting_sales_approval → approved_by_sales_manager
    public function salesApprove(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canSalesApproveOrReject()) {
            return back()->with('error','این پیش‌فاکتور در انتظار تایید مدیر فروش نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::ApprovedBySalesManager,
        ]);

        return back()->with('success','تایید مدیر فروش ثبت شد.');
    }

    // waiting_sales_approval → rejected_by_sales_manager
    public function salesReject(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canSalesApproveOrReject()) {
            return back()->with('error','این پیش‌فاکتور در انتظار تایید مدیر فروش نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::RejectedBySalesManager,
        ]);

        return back()->with('success','پیش‌فاکتور توسط مدیر فروش رد شد.');
    }

    // approved_by_sales_manager → confirmed (ارسال به مشتری)
    public function sendToCustomer(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canSendToCustomer()) {
            return back()->with('error','این پیش‌فاکتور در وضعیت مناسب برای ارسال به مشتری نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::Confirmed,
        ]);

        return back()->with('success','پیش‌فاکتور برای مشتری ارسال شد.');
    }


    public function approveSales(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canApproveBySalesManager()) {
            return back()->with('error','این پیش‌فاکتور هنوز برای تایید مدیر فروش آماده نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::ApprovedBySalesManager,
        ]);

        return back()->with('success','تایید مدیر فروش انجام شد.');
    }


    public function acceptByCustomer(PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canAcceptOrRejectByCustomer()) {
            return back()->with('error','این پیش‌فاکتور در وضعیت انتظار تایید مشتری نیست.');
        }

        $pre_invoice->update([
            'status' => PreInvoiceStatus::AcceptedByCustomer,
        ]);

        return back()->with('success','تایید مشتری ثبت شد.');
    }

    public function rejectByCustomer(Request $request, PreInvoice $pre_invoice)
    {
        if (! $pre_invoice->canAcceptOrRejectByCustomer()) {
            return back()->with('error','این پیش‌فاکتور در وضعیت انتظار تایید مشتری نیست.');
        }

        // اگر reason برای رد داری، اینجا ذخیره کن
        $pre_invoice->update([
            'status' => PreInvoiceStatus::RejectedByCustomer,
        ]);

        return back()->with('success','رد مشتری ثبت شد.');
    }

    public function create()
    {
        // $this->authorize('create', PreInvoice::class);

        $customers = Customer::orderBy('id','desc')->get();
        $sources   = Source::orderBy('id','desc')->get();

        return view('pre_invoices.create', compact('customers','sources'));
    }

    public function store(Request $request)
    {
        // $this->authorize('create', PreInvoice::class);

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'source_id'   => 'nullable|exists:sources,id',
            'type'        => 'required|in:normal,formal,export',
        ]);

        $preInvoice = PreInvoice::create([
            'customer_id'  => $data['customer_id'],
            'source_id'    => $data['source_id'] ?? null,
            'type'         => $data['type'],
            'status'       => 'draft',
            'total_amount' => 0,
            'formal_extra' => null,
            'created_by'   => auth()->id(),
        ]);

        return redirect()->route('pre-invoices.edit',$preInvoice->id)->with('success','پیش‌فاکتور ایجاد شد، لطفاً آیتم‌ها را اضافه کنید.');
    }

    // public function edit(PreInvoice $pre_invoice)
    // {
    //     $this->authorize('update', $pre_invoice);

    //     $customers = Customer::orderBy('id','desc')->get();
    //     $sources   = Source::orderBy('id','desc')->get();
    //     $preInvoice = $pre_invoice->load('items.product');

    //     return view('pre_invoices.edit', compact('preInvoice','customers','sources'));
    // }

    public function edit(PreInvoice $pre_invoice)
    {
        $this->authorize('update', $pre_invoice);

        $customers = Customer::orderBy('id','desc')->get();
        $sources   = Source::orderBy('id','desc')->get();
        $buyers    = User::whereHas('roles', function($q){
                        $q->where('name','purchase_expert');
                    })->orderBy('name')->get();

        // ✅ اضافه کردن دسته‌بندی‌ها (فقط والدین)
        $categories = \App\Models\ProductCategory::whereNull('parent_id')
                                        ->orderBy('name')
                                        ->get();

        $preInvoice = $pre_invoice->load('items.purchaseAssignments.buyer','items.product');

        return view('pre_invoices.edit', compact('preInvoice','customers','sources','buyers', 'categories'));
    }

    
    // public function edit(PreInvoice $pre_invoice)
    // {
    //     $this->authorize('update', $pre_invoice);

    //     $customers = Customer::orderBy('id','desc')->get();
    //     $sources   = Source::orderBy('id','desc')->get();
    //     $buyers    = User::whereHas('roles', function($q){
    //                         $q->where('name','purchase_expert'); // اسم نقش کارشناس خرید
    //                     })->orderBy('name')->get();

    //     // $preInvoice = $pre_invoice->load('items.product.purchaseAssignments.buyer','items.product');
    //     $preInvoice = $pre_invoice->load('items.purchaseAssignments.buyer','items.product');


    //     return view('pre_invoices.edit', compact('preInvoice','customers','sources','buyers'));
    // }
    

    public function update(Request $request, PreInvoice $pre_invoice)
    {
        $this->authorize('update', $pre_invoice);

        $data = $request->validate([
            // 'customer_id' => 'required|exists:customers,id',
            // 'source_id'   => 'nullable|exists:sources,id',
            'type'        => 'required|in:normal,formal,export',
        ]);

        $pre_invoice->update($data);

        // محاسبه مبلغ بعداً در گام آیتم‌ها
        return redirect()->route('pre-invoices.edit',$pre_invoice->id)
            ->with('success','پیش‌فاکتور به‌روزرسانی شد.');
    }


    public function purchaseIndex()
    {
        $query = PreInvoice::query()
            ->where('direction', 'purchase')
            ->with(['source','buyer','salePreInvoice']);

        // اگر فقط مدیر خرید ببیند، می‌توانی فیلتر بر اساس نقش/کاربر هم بگذاری
        // مثلا:
        // if (auth()->user()->hasRole('purchase_manager')) { ... }

        $purchasePreInvoices = $query->orderByDesc('id')->paginate(20);

        return view('pre_invoices.purchase_index', compact('purchasePreInvoices'));
    }



    public function saveSalePrices(Request $request, PreInvoice $pre_invoice)
    {
        $user = auth()->user();

        abort_unless($pre_invoice->direction === 'sale', 404);

        $data = $request->validate([
            'items'                          => 'required|array',
            'items.*.sale_unit_price'        => 'nullable|numeric|min:0',
            'items.*.profit_percent'         => 'nullable|numeric',
        ]);

        foreach ($pre_invoice->items as $item) {
            $row = $data['items'][$item->id] ?? null;
            if (! $row) {
                continue;
            }

            // اگر قیمت خالی است، رد شو
            if (! isset($row['sale_unit_price'])) {
                continue;
            }

            $price = $row['sale_unit_price'];

            // اگر قیمت عددی است، ذخیره کن
            if ($price !== null) {
                $item->sale_unit_price = $price;
                $item->total           = $item->quantity * $price;

                // اگر خواستی درصد سود را هم نگه داری:
                if (isset($row['profit_percent'])) {
                    $item->profit_percent = $row['profit_percent'];
                }

                $item->save();
            }
        }

        // جمع کل پیش‌فاکتور
        $pre_invoice->total_amount = $pre_invoice->items()->sum('total');

        if ($pre_invoice->type === 'formal') {
            $pre_invoice->formal_extra = round($pre_invoice->total_amount * 0.1);
        } else {
            $pre_invoice->formal_extra = null;
        }

        $pre_invoice->status = 'priced_by_sales';
        $pre_invoice->save();

        return redirect()
            ->route('pre-invoices.show',$pre_invoice->id)
            ->with('success','قیمت‌های فروش ذخیره شد و پیش‌فاکتور برای تأیید آماده است.');
    }


    public function pricedBySalesIndex()
    {
        // $user = auth()->user();
        // abort_unless($user->isManagement(), 403);

        $preInvoices = PreInvoice::sales()
            ->where('status','priced_by_sales')
            ->with('customer')
            ->orderByDesc('id')
            ->paginate(30);

        return view('sales_manager.pre_invoices.priced', compact('preInvoices'));
    }

    // PreInvoiceController.php
    public function salesWaitingApproval()
    {
        $user = auth()->user();
        abort_unless($user->isManagement(), 403);

        $preInvoices = PreInvoice::sales()
            ->where('status','waiting_sales_approval')
            ->with('customer')
            ->orderByDesc('id')
            ->paginate(30);

        return view('sales_manager.pre_invoices.waiting_approval', compact('preInvoices'));
    }


    // PreInvoiceController.php
    public function financeFromApprovedSales()
    {
        // $user = auth()->user();
        // abort_unless($user->isManagement(), 403); // یا isFinance()

        $preInvoices = PreInvoice::sales()
            ->where('status','approved_by_sales_manager')
            ->with('customer')
            ->orderByDesc('id')
            ->paginate(30);

        return view('finance.pre_invoices.approved_by_sales', compact('preInvoices'));
    }


    public function financeCreateInvoice(PreInvoice $pre_invoice)
    {
        if ($pre_invoice->status !== 'approved_by_sales_manager') {
            return back()->with('error','این پیش‌فاکتور در مرحله تأیید نهایی فروش نیست.');
        }

        // ایجاد فاکتور
        $invoice = Invoice::create([
            'customer_id'  => $pre_invoice->customer_id,
            'type'         => $pre_invoice->type,
            'status'       => 'awaiting_payment',
            'total_amount' => $pre_invoice->total_amount,
            'formal_extra' => $pre_invoice->formal_extra,
            'created_by'   => auth()->id(),
        ]);

        foreach ($pre_invoice->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'unit_price' => $item->sale_unit_price,
                'attributes' => $item->attributes,
                'total'      => $item->total,
            ]);
        }

        // بستن پیش‌فاکتور
        $pre_invoice->status = 'closed';
        $pre_invoice->save();

        return redirect()
            ->route('invoices.show',$invoice->id)
            ->with('success','فاکتور نهایی ایجاد شد.');
    }

    public function customerApprove(PreInvoice $pre_invoice)
    {
        abort_unless($pre_invoice->direction === 'sale', 404);

        $pre_invoice->status = \App\Enums\PreInvoiceStatus::CustomerApproved;
        $pre_invoice->customer_reject_reason = null;
        $pre_invoice->save();

        return back()->with('success', 'این پیش‌فاکتور توسط مشتری تایید شد.');
    }

    public function customerReject(Request $request, PreInvoice $pre_invoice)
    {
        abort_unless($pre_invoice->direction === 'sale', 404);

        $data = $request->validate([
            'customer_reject_reason' => ['required','string','max:5000'],
        ]);

        $pre_invoice->status = \App\Enums\PreInvoiceStatus::CustomerRejected;
        $pre_invoice->customer_reject_reason = $data['customer_reject_reason'];
        $pre_invoice->save();

        return back()->with('success', 'این پیش‌فاکتور توسط مشتری رد شد.');
    }

    
    public function sendToFinance(PreInvoice $pre_invoice, Request $request)
    {
        // اگر لاگین و نقش‌ها مهم است:
        // $this->authorize('send-to-finance', $pre_invoice);

        // چک پیش‌شرط: مثلاً داشتن برنامه پرداخت و پیش‌پرداخت ثبت‌شده
        if (! $pre_invoice->hasAdvancePaidPendingFinance()) {
            return back()->with('error', 'پیش‌پرداخت لازم برای ارسال به مالی ثبت نشده است.');
        }
        // dd($pre_invoice->id);

        // تغییر وضعیت پیش‌فاکتور به «در انتظار تایید مالی»
        // $pre_invoice->status = 'WaitingFinance'; 
        $pre_invoice->status = PreInvoiceStatus::AdvanceWaitingFinance;
        $pre_invoice->save();

        return back()->with('success', 'پیش‌فاکتور برای تایید پیش‌پرداخت به واحد مالی ارسال شد.');
    }

    public function sendToFinanceForBuy(PreInvoice $pre_invoice, Request $request)
    {
        // اگر لاگین و نقش‌ها مهم است:
        // $this->authorize('send-to-finance', $pre_invoice);

        // چک پیش‌شرط: مثلاً داشتن برنامه پرداخت و پیش‌پرداخت ثبت‌شده
        if (! $pre_invoice->hasAdvancePaidPendingFinance()) {
            return back()->with('error', 'پیش‌پرداخت لازم برای ارسال به مالی ثبت نشده است.');
        }
        // dd($pre_invoice->id);

        // تغییر وضعیت پیش‌فاکتور به «در انتظار تایید مالی»
        // $pre_invoice->status = 'WaitingFinance'; 
        $pre_invoice->status = PreInvoiceStatus::AdvanceWaitingFinance;
        $pre_invoice->save();

        return back()->with('success', 'پیش‌فاکتور برای تایید پیش‌پرداخت به واحد مالی ارسال شد.');
    }

    
    public function advanceConfirm(PreInvoice $pre_invoice)
    {
        // فقط اگر در مرحله انتظار تایید مالی پیش‌پرداخت است
        if ($pre_invoice->status !== PreInvoiceStatus::AdvanceWaitingFinance) {
            return back()->with('error', 'پیش‌فاکتور در مرحله تایید پیش‌پرداخت نیست.');
        }

        // باید حداقل یک پرداخت تایید شده باشد
        if (! $pre_invoice->hasConfirmedPayments()) {
            return back()->with('error', 'هنوز هیچ واریزی توسط مالی تایید نشده است.');
        }

        DB::transaction(function () use ($pre_invoice) {
            // اینجا می‌توانی چک‌های تکمیلی هم انجام دهی (مثلاً مجموع حداقل X باشد)

            $pre_invoice->status = PreInvoiceStatus::AdvanceFinanceApproved;
            $pre_invoice->save();
        });

        return back()->with('success', 'پیش‌پرداخت این پیش‌فاکتور به صورت کلی تایید شد.');
    }
   
    public function goToBuying(PreInvoice $pre_invoice)
    {
        // فقط اگر پیش‌پرداخت از نظر مالی به صورت کلی تایید شده است
        if ($pre_invoice->status !== PreInvoiceStatus::AdvanceFinanceApproved) {
            return back()->with('error', 'این پیش‌فاکتور هنوز در مرحله تایید نهایی پیش‌پرداخت نیست.');
        }

        DB::transaction(function () use ($pre_invoice) {
            $pre_invoice->status = PreInvoiceStatus::WaitingPurchaseExecution; // یا هر استیت شروع خرید
            $pre_invoice->save();
        });

        return back()->with('success', 'پیش‌فاکتور به مرحله خرید منتقل شد.');
    }

    public function goToSelling(PreInvoices $purchasePreInvoice)
    {
        // 1) این پیش‌فاکتور باید خرید باشد
        abort_unless($purchasePreInvoice->direction === 'purchase', 404);

        // 2) چک کن برنامه پرداخت کامل و تایید شده است (به طراحی خودت بستگی دارد)
        // مثلا همه plans این pre_invoice یا حداقل یکی status = 'confirmed' داشته باشد

        // 3) وزن/قیمت آیتم‌ها در خرید نهایی شده باشد
        foreach ($purchasePreInvoice->items as $item) {
            // اینجا می‌توانی چک کنی purchase_unit_price و weight نهایی ست شده‌اند
        }

        // 4) لینک به پیش‌فاکتور فروش
        $salePre = $purchasePreInvoice->salePreInvoice;
        if ($salePre) {
            // اگر همه آیتم‌های این فروش خریدشان نهایی شده:
            $allBought = $salePre->items->every(function ($item) {
                return $item->chosenPurchaseAssignment && $item->purchase_unit_price;
            });

            if ($allBought) {
                // استیت فروش را ببر مرحله بعد
                $salePre->status = 'buying'; // یا 'approvedsalespurchase' / هر چیزی که مرحله بعد توست
                $salePre->save();
            }
        }

        // خود پیش‌فاکتور خرید را هم مثلا:
        $purchasePreInvoice->status = 'bought';
        $purchasePreInvoice->save();

        return back()->with('success', 'خرید تایید و فروش به مرحله بعد منتقل شد.');
    }


    public function startPurchasing(PreInvoice $pre_invoice)
    {
        if ($pre_invoice->status !== PreInvoiceStatus::AdvanceFinanceApproved) {
            return back()->with('error', 'ابتدا باید پیش‌پرداخت به صورت مالی تایید شود.');
        }

        $pre_invoice->status = PreInvoiceStatus::WaitingPurchaseExecution;
        $pre_invoice->save();

        return back()->with('success', 'پیش‌فاکتور به مرحله ارجاع به کارشناسان خرید رفت.');
    }

    public function approveFullPurchase(PreInvoice $preInvoice)
    {
        // فقط روی پیش‌فاکتور فروش
        // abort_unless($preInvoice->direction === 'sale', 404);

        // چک پرمیشن مدیر خرید
        // $this->authorize('approveFullPurchase', $preInvoice);

        // باید همه خریدها واقعاً تکمیل شده باشند
        $preInvoice->load('purchasePreInvoices.purchaseItems');

        if (! $preInvoice->isPurchaseFullyCompleted()) {
            return back()->with('error', 'هنوز خرید همه آیتم‌ها تکمیل نشده است.');
        }

        // ست کردن وضعیت: خرید تکمیل شده
        $preInvoice->status = PreInvoiceStatus::PurchaseCompleted;

        // به محض تایید مدیر خرید، می‌خواهی به مرحله قیمت‌گذاری فروش برود
        // یا مستقیم به PricedBySales یا WaitingSalesApproval، طبق بیزینس‌لاگیک:
        // مثال: بعد از تکمیل خرید، مسئول فروش باید قیمت نهایی را بزند:
        // $preInvoice->status = PreInvoiceStatus::PricedBySales->value;

        $preInvoice->save();

        return back()->with('success', 'خرید کل پیش‌فاکتور توسط مدیر خرید تایید شد و به فروش ارجاع گردید.');
    }

    
    public function postPurchaseSalesApprove(PreInvoice $preInvoice)
    {
        // abort_unless($preInvoice->direction === 'sale', 404);

        // $this->authorize('postPurchaseSalesApprove', $preInvoice);

        // // می‌توانی در صورت نیاز چند ولیدیشن اضافی هم انجام دهی
        // // مثلا: همه خریدها کامل باشند
        // $preInvoice->load('purchasePreInvoices.purchaseItems');

        if (! $preInvoice->isPurchaseFullyCompleted()) {
            return back()->with('error', 'خرید همه آیتم‌ها هنوز تکمیل نشده است.');
        }

        $preInvoice->status = PreInvoiceStatus::PostPurchaseSalesApproved;
        $preInvoice->save();

        return back()->with('success', 'شرایط توسط کارشناس فروش تایید شد. حالا می‌توانید درخواست فرم حمل ثبت کنید.');
    }

    public function requestShipping(PreInvoice $preInvoice)
    {
        // abort_unless($preInvoice->direction === 'sale', 404);

        // $this->authorize('requestShipping', $preInvoice);

        // اگر فرم/اطلاعات اضافی لازم داری (مثلاً توضیح)، اینجا validate کن

        $preInvoice->status = PreInvoiceStatus::ShippingRequested;
        $preInvoice->save();

        return back()->with('success', 'درخواست فرم حمل ثبت شد و برای واحد حمل/لجستیک ارسال گردید.');
    }

    // public function salesManagerDecision(Request $request, PreInvoice $preInvoice)
    // {
    //     $data = $request->validate([
    //         'decision' => 'required|in:wait,approve_and_invoice',
    //     ]);

    //     $wait = $data['decision'] === 'wait';

    //     $preInvoice->markAfterSalesManagerDecision($wait);

    //     if (!$wait) {
    //         // اینجا تبدیل به فاکتور را صدا می‌زنیم (بخش بعدی)
    //         app(\App\Services\InvoiceService::class)->convertPreInvoiceToInvoice($preInvoice);
    //     }

    //     return back()->with('success', 'تصمیم مدیر فروش ثبت شد.');
    // }

     public function salesManagerDecision(Request $request, PreInvoice $preInvoice, InvoiceService $invoiceService) 
     {
        $data = $request->validate([
            'decision' => 'required|in:wait,approve_and_invoice',
        ]);

        // اگر مدیر فروش گفته "فعلاً صبر کن"
        if ($data['decision'] === 'wait') {
            // منطق ساده: فقط وضعیت را بر اساس حمل به‌روز کن
            $preInvoice->markAfterSalesManagerDecision(true);

            return back()->with('success', 'تصمیم مدیر فروش ثبت شد و پیش‌فاکتور در وضعیت فعلی باقی ماند.');
        }

        // تصمیم: "تأیید و تبدیل به فاکتور"
        // ابتدا وضعیت پیش‌فاکتور را به حالت مناسب برای تبدیل ببریم
        $preInvoice->markAfterSalesManagerDecision(false);

        // سپس تبدیل واقعی به فاکتور
        $invoice = $invoiceService->convertPreInvoiceToInvoice($preInvoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'پیش‌فاکتور توسط مدیر فروش تأیید و به فاکتور تبدیل شد.');
    }



}


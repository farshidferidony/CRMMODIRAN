@php
    $remaining = $remaining ?? ($pre_invoice->total_amount - ($totalPaid ?? 0));
    $totalPaid = $totalPaid ?? 0;

    $user = auth()->user();

    // رول‌هایی که همیشه دسترسی دارند
    $isTopManager = $user->hasRole(['ceo','it_manager']);

    use App\Enums\PreInvoiceStatus;
@endphp

@extends('layouts.master')
@section('title','پیش‌فاکتور #'.$pre_invoice->id)

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>اطلاعات پیش‌فاکتور</span>
            <span class="badge bg-info">{{ $pre_invoice->status_label }}</span>
        </div>
        

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success mb-2">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-2">{{ session('error') }}</div>
            @endif

            <p>مشتری:
                {{ $pre_invoice->customer->display_name ?? '-' }}
            </p>
            <p>مبلغ کل: {{ number_format($pre_invoice->total_amount) }}</p>

            <div class="mt-3 d-flex flex-wrap gap-2">

            {{-- 4 → 5: ارسال به خرید (کارشناس فروش) --}}
            @if($pre_invoice->canSendToPurchase())
                <form method="POST" action="{{ route('pre_invoices.send_to_purchase',$pre_invoice) }}">
                    @csrf
                    <button class="btn btn-warning btn-sm">
                        ارسال به خرید
                    </button>
                </form>
            @endif
            
            @if($pre_invoice->status === PreInvoiceStatus::ApprovedManager
                && (!$pre_invoice->canBePricedBySales()) && ($user->hasRole(['finance_manager','finance_expert']) || $isTopManager))
                {{-- لینک بررسی برای کارشناس خرید / مدیر خرید --}}
                <strong>پیش‌فاکتورهای خرید در انتظار تایید مالی:</strong>
                <ul class="mt-2">
                    @foreach($pre_invoice->purchasePreInvoices as $ppi)
                        <li class="small mb-1">
                            پیش‌فاکتور خرید #{{ $ppi->id }}
                            –
                            <a href="{{ route('purchase_pre_invoices.purchase_show', $ppi->id) }}"
                            target="_blank"
                            class="btn btn-link btn-sm p-0 align-baseline">
                                مشاهده 
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif




            {{-- 5 → priced_by_purchase: ثبت قیمت‌های خرید (کارشناسان خرید) --}}
            @if($pre_invoice->canBePricedByPurchase())

                {{-- لیست آیتم‌ها و کارشناسان خریدی که روی هر آیتم قیمت داده‌اند --}}
                <table class="table table-bordered align-middle mt-3">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>قیمت خرید نهایی</th>
                        <th>کارشناسان خرید / قیمت‌ها / وضعیت</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pre_invoice->saleItems as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @if($item->purchase_unit_price)
                                    {{ number_format($item->purchase_unit_price) }}
                                @else
                                    <span class="text-danger">هنوز نهایی نشده</span>
                                @endif
                            </td>
                            <td>
                                @if($item->purchaseAssignments->isEmpty())
                                    <span class="text-muted">ارجاعی ثبت نشده</span>
                                @else
                                    <ul class="mb-0 list-unstyled">
                                        @foreach($item->purchaseAssignments as $pa)
                                            @php
                                                // قیمت پیشنهادی کارشناس روی assignment
                                                $price   = $pa->unit_price ?? null;  // ← اگر نام فیلد چیز دیگری است، اینجا عوض کن
                                                // تشخیص اینکه این پیشنهاد، انتخاب نهایی است یا نه
                                                $isChosen = $pa->is_chosen ?? false; // یا مثلاً: $pa->status === 'chosen';
                                            @endphp

                                            <li class="mb-1">
                                                {{-- نام کارشناس و منبع --}}
                                                <strong>{{ $pa->buyer?->name ?? 'بدون نام' }}</strong>
                                                @if($pa->source)
                                                    - {{ $pa->source->name }}
                                                @endif

                                                {{-- وضعیت --}}
                                                <span class="badge bg-secondary ms-1">
                                                    وضعیت: {{ $pa->status }}
                                                </span>

                                                {{-- قیمت پیشنهادی این کارشناس --}}
                                                @if($price !== null)
                                                    <span class="ms-2">
                                                        قیمت پیشنهادی:
                                                        <span class="{{ $isChosen ? 'text-success fw-bold' : '' }}">
                                                            {{ number_format($price) }}
                                                        </span>
                                                        @if($isChosen)
                                                            <span class="badge bg-success">انتخاب نهایی</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-muted ms-2">قیمتی ثبت نشده</span>
                                                @endif

                                                {{-- لینک قیمت‌گذاری/ویرایش همین ارجاع --}}
                                                <a href="{{ route('buyer.assignments.edit',$pa->id) }}"
                                                class="btn btn-sm btn-primary ms-2">
                                                    قیمت‌گذاری/ویرایش
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @php
                    // اگر متد را در مدل PreInvoice تعریف کرده‌ای:
                    // public function allItemsPurchasePriced(): bool {
                    //     return $this->saleItems()->whereNull('purchase_unit_price')->count() === 0;
                    // }
                    $allPriced = $pre_invoice->allItemsPurchasePriced();
                @endphp

                {{-- فرم ثبت نهایی قیمت‌های خرید (فقط وقتی همه آیتم‌ها قیمت خرید نهایی دارند) --}}
                <form method="POST" action="{{ route('pre_invoices.price_by_purchase',$pre_invoice) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm" @if(!$allPriced) disabled @endif>
                        ثبت قیمت‌های خرید
                    </button>
                </form>

                {{-- پیام راهنما اگر هنوز قیمت‌گذاری کامل نشده --}}
                @if(!$allPriced)
                    <small class="text-danger d-block mt-1">
                        تا زمانی که قیمت خرید برای همه آیتم‌ها نهایی نشده باشد، امکان ثبت قیمت‌های خرید وجود ندارد.
                    </small>
                @endif

                {{-- لینک بررسی برای کارشناس خرید / مدیر خرید --}}
                <a href="{{ route('purchase-manager.pre-invoices.review', $pre_invoice->id) }}"
                class="btn btn-outline-primary btn-sm mt-2">
                    بررسی خرید
                </a>

            @endif


            {{-- priced_by_purchase → approved_manager: تایید مدیر خرید --}}
            @if($pre_invoice->canApproveByPurchaseManager())
                <form method="POST" action="{{ route('pre_invoices.approve_purchase',$pre_invoice) }}">
                    @csrf
                    <button class="btn btn-success btn-sm">
                        تایید مدیر خرید
                    </button>
                </form>

                {{-- لینک بررسی جزئیات قیمت‌ها برای مدیر خرید --}}
                <a href="{{ route('purchase-manager.pre-invoices.review', $pre_invoice->id) }}"
                class="btn btn-outline-success btn-sm">
                    بررسی و انتخاب قیمت‌ها
                </a>
            @endif

            {{-- approved_manager → priced_by_sales: ثبت قیمت‌های فروش (کارشناس فروش) --}}
            @php 
                // اگر متد در مدل تعریف شده:
                $allSalesPricesSet = $pre_invoice->allItemsSalePriced();
            @endphp

            @if($pre_invoice->canBePricedBySales())
                <form method="POST" action="{{ route('pre_invoices.price_by_sales',$pre_invoice) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-primary btn-sm" @if(! $allSalesPricesSet) disabled @endif>
                        ثبت قیمت‌های فروش (تایید نهایی)
                    </button>
                </form>

                @if(! $allSalesPricesSet)
                    <small class="text-danger d-block mt-1">
                        تا زمانی که قیمت فروش برای همه آیتم‌ها ثبت نشده باشد، امکان تایید نهایی وجود ندارد.
                    </small>
                @endif

                {{-- لینک بررسی / فرم قیمت‌گذاری فروش در صفحه جدا --}}
                <a href="{{ route('pre_invoices.edit_sale_prices', $pre_invoice) }}"
                class="btn btn-outline-primary btn-sm">
                    فرم قیمت‌گذاری فروش
                </a>
            @endif



            {{-- priced_by_sales → waiting_sales_approval: ارسال برای تایید مدیر فروش --}}
            @if($pre_invoice->canSendToSalesApproval())
                <form method="POST" action="{{ route('pre_invoices.send_to_sales_approval',$pre_invoice) }}">
                    @csrf
                    <button class="btn btn-info btn-sm">
                        ارسال برای تایید مدیر فروش
                    </button>
                </form>

                {{-- لینک مخصوص مدیر فروش برای بررسی قبل از تایید --}}
                <a href="{{ route('sales-manager.pre-invoices.waiting-approval') }}"
                class="btn btn-outline-info btn-sm">
                    لیست پیش‌فاکتورهای در انتظار تایید
                </a>
            @endif

            {{-- waiting_sales_approval → تایید/رد مدیر فروش --}}
            @if($pre_invoice->canSalesApproveOrReject())
                <form method="POST" action="{{ route('pre_invoices.sales_approve',$pre_invoice) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-success btn-sm">
                        تایید مدیر فروش
                    </button>
                </form>
                <form method="POST" action="{{ route('pre_invoices.sales_reject',$pre_invoice) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-danger btn-sm">
                        رد مدیر فروش
                    </button>
                </form>

                {{-- لینک بررسی برای مدیر فروش روی همین پیش‌فاکتور --}}
                <a href="{{ route('pre-invoices.edit',$pre_invoice->id) }}"
                class="btn btn-outline-secondary btn-sm">
                    ویرایش پیش‌فاکتور
                </a>
            @endif

            {{-- approved_by_sales_manager → confirmed (ارسال به مشتری / چاپ) --}}
            @if($pre_invoice->canSendToCustomer())
                <form method="POST" action="{{ route('pre_invoices.send_to_customer',$pre_invoice) }}">
                    @csrf
                    <button class="btn btn-secondary btn-sm">
                        ارسال به مشتری / آماده چاپ
                    </button>
                </form>

                {{-- لینک چاپ/نمایش پیش‌فاکتور برای مشتری (فقط اگر آماده باشد) --}}
                @if($pre_invoice->isReadyForCustomer())
                    <a href="{{ route('pre_invoices.print',$pre_invoice) }}"
                    class="btn btn-outline-secondary btn-sm">
                        چاپ پیش‌فاکتور
                    </a>
                @endif
            @endif

            @if($pre_invoice->canSetCustomerDecision())
            <div class="card mt-3">
                <div class="card-header">نتیجه از سمت مشتری</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pre_invoices.customer_approve', $pre_invoice) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            تایید توسط مشتری
                        </button>
                    </form>

                    <button class="btn btn-danger btn-sm" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#customerRejectForm">
                        عدم تایید مشتری
                    </button>

                    <div id="customerRejectForm" class="collapse mt-2">
                        <form method="POST" action="{{ route('pre_invoices.customer_reject', $pre_invoice) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">دلیل عدم تایید مشتری</label>
                                <textarea name="customer_reject_reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button class="btn btn-danger btn-sm">ثبت عدم تایید</button>
                        </form>
                    </div>

                    @if($pre_invoice->status === \App\Enums\PreInvoiceStatus::CustomerRejected && $pre_invoice->customer_reject_reason)
                        <div class="alert alert-warning mt-2 mb-0">
                            دلیل رد مشتری: {{ $pre_invoice->customer_reject_reason }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($pre_invoice->status === \App\Enums\PreInvoiceStatus::CustomerApproved)
            {{-- برنامه داینامیک پرداخت (فقط سمت کارشناس برنامه‌ریز) --}}
            @if($remaining > 0)
            <div class="card mt-4" id="payment-plan-card"
                data-pre-invoice-total="{{ $pre_invoice->total_amount }}">
                <div class="card-header">
                    برنامه پرداخت (مبلغ پیش‌فاکتور: {{ number_format($pre_invoice->total_amount) }})
                </div>
                <div class="card-body">
                    <div class="alert alert-info" id="plan-status">
                        جمع اقساط: <span id="plan-sum">0</span> از {{ number_format($pre_invoice->total_amount) }} ریال
                    </div>

                    <table class="table table-bordered align-middle" id="plan-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>مبلغ قسط</th>
                            <th>تاریخ پرداخت</th>
                            <th>نوع پرداخت</th>
                            <th>حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-secondary mb-2" id="add-plan-row">
                        افزودن ردیف جدید
                    </button>

                    <button type="button" class="btn btn-success" id="save-plan" disabled>
                        ثبت برنامه پرداخت
                    </button>

                    <div class="text-danger mt-2" id="plan-error" style="display:none;"></div>
                    <div class="text-success mt-2" id="plan-success" style="display:none;"></div>
                </div>
            </div>
            @endif

            {{-- اقساط برنامه‌ریزی‌شده برای پیش‌فاکتور --}}
            @if($pre_invoice->paymentPlans && $pre_invoice->paymentPlans->count())
                <div class="card mt-4">
                    <div class="card-header">اقساط برنامه‌ریزی‌شده</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>مبلغ قسط</th>
                                <th>تاریخ برنامه‌ریزی‌شده</th>
                                <th>نوع پرداخت</th>
                                <th>توضیحات</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pre_invoice->paymentPlans as $plan)
                                <tr>
                                    <td>{{ $plan->id }}</td>
                                    <td>{{ number_format($plan->amount) }}</td>
                                    <td>{{ $plan->scheduled_date }}</td>
                                    <td>{{ $plan->payment_type }}</td>
                                    <td>{{ $plan->note }}</td>
                                    <td>
                                        @if($plan->is_completed)
                                            <span class="text-success">قسط بسته شده</span>
                                        @else
                                            {{-- فرم اعلام پرداخت واقعی توسط کارشناس فروش --}}
                                            <form method="POST"
                                                action="{{ route('plans.pre-pay', $plan->id) }}"
                                                enctype="multipart/form-data"
                                                class="d-flex gap-1">
                                                @csrf
                                                <input type="number" step="0.01" name="paid_amount"
                                                    class="form-control form-control-sm"
                                                    placeholder="مبلغ واقعی" required>
                                                <input type="date" name="actual_paid_date"
                                                    class="form-control form-control-sm"
                                                    value="{{ now()->toDateString() }}" required>
                                                <input type="file" name="receipt"
                                                    class="form-control form-control-sm"
                                                    accept="image/*,application/pdf">
                                                <button class="btn btn-sm btn-success">
                                                    ثبت پرداخت
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($pre_invoice->hasAdvancePaidPendingFinance() && $pre_invoice->status !== 'WaitingFinance')
                    <form method="POST"
                        action="{{ route('pre_invoices.send_to_finance', $pre_invoice->id) }}"
                        class="d-inline">
                        @csrf
                        <button class="btn btn-warning">
                            ارسال به مالی برای تایید پیش‌پرداخت
                        </button>
                    </form>
                @endif


            @endif



        @endif

        {{-- دکمه تایید کلی پیش‌پرداخت --}}
        @if($pre_invoice->status === PreInvoiceStatus::AdvanceWaitingFinance)
            <div class="mt-3">

                {{-- دکمه تایید کلی (فقط اگر حداقل یک پرداخت تایید شده باشد) --}}
                <form method="POST"
                    action="{{ route('pre_invoices.advance_confirm', $pre_invoice->id) }}"
                    class="d-inline">
                    @csrf
                    <button class="btn btn-success"
                            @if(! $pre_invoice->hasConfirmedPayments()) disabled @endif>
                        تایید واریزی
                    </button>
                </form>

                {{-- دکمه باز/بستن لیست اقساط / واریزی‌ها برای تایید آیتمی --}}
                <button class="btn btn-outline-primary ms-2"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#finance-payments-collapse"
                        aria-expanded="false"
                        aria-controls="finance-payments-collapse">
                    ویرایش اقساط برنامه‌ریزی‌شده
                </button>

                {{-- بخش آبشاری: لیست پرداخت‌ها برای تایید / رد توسط مالی --}}
                <div class="collapse mt-3" id="finance-payments-collapse">
                    <div class="card">
                        <div class="card-header">
                            لیست واریزی‌ها (پیش‌پرداخت)
                        </div>
                        <div class="card-body p-0">
                            @php
                                $payments = $pre_invoice->paymentsForFinance();
                            @endphp

                            @if($payments->isEmpty())
                                <div class="p-3 text-muted">
                                    تاکنون پرداختی برای این پیش‌فاکتور ثبت نشده است.
                                </div>
                            @else
                                <table class="table table-bordered table-sm mb-0 align-middle">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>مبلغ برنامه‌ای</th>
                                        <th>مبلغ پرداخت‌شده</th>
                                        <th>تاریخ برنامه‌ریزی‌شده</th>
                                        <th>تاریخ پرداخت</th>
                                        <th>نوع</th>
                                        <th>وضعیت</th>
                                        <th>سند</th>
                                        <th>عملیات مالی</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($payments as $pay)
                                        <tr>
                                            <td>{{ $pay->id }}</td>
                                            <td>{{ number_format($pay->amount) }}</td>
                                            <td>{{ $pay->paid_amount ? number_format($pay->paid_amount) : '-' }}</td>
                                            <td>{{ $pay->scheduled_date }}</td>
                                            <td>{{ $pay->actual_paid_date }}</td>
                                            <td>{{ $pay->payment_type }}</td>
                                            <td>{{ $pay->status }}</td>
                                            <td>
                                                @if($pay->receipt_path)
                                                    <a href="{{ asset('storage/' . $pay->receipt_path) }}"
                                                    target="_blank">
                                                        مشاهده
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pay->status !== 'confirmed')
                                                    {{-- تایید مالی این واریزی --}}
                                                    <form method="POST"
                                                        action="{{ route('finance.payments.confirm', $pay->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success mb-1">
                                                            تایید
                                                        </button>
                                                    </form>

                                                    {{-- رد مالی این واریزی --}}
                                                    <form method="POST"
                                                        action="{{ route('finance.payments.reject', $pay->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="finance_reject_reason"
                                                            value="رد توسط مالی در مرحله پیش‌پرداخت">
                                                        <button class="btn btn-sm btn-danger mb-1">
                                                            رد
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-success">تایید شده</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        @endif


        {{-- وقتی پیش‌پرداخت نهایی شد و باید به خرید برویم --}}
        @if($pre_invoice->status === PreInvoiceStatus::AdvanceFinanceApproved)
            <form method="POST"
                action="{{ route('pre_invoices.go_to_buying', $pre_invoice->id) }}"
                class="mt-3">
                @csrf
                <button class="btn btn-primary">
                    انتقال به مرحله خرید
                </button>
            </form>
        @endif
        

        @if($pre_invoice->purchasePreInvoices->count() && 
        in_array($pre_invoice->status, [
            PreInvoiceStatus::WaitingPurchaseExecution,
            PreInvoiceStatus::Purchasing,
        ])
        )

            @php
                $allPurchasesCompleted = true;
            @endphp

            <div class="alert alert-info mt-3">
                پیش‌فاکتور در حال خرید توسط تیم خرید است. لطفاً تا تکمیل خرید صبر کنید.
            </div>

            <div class="mt-2">
                <strong>پیش‌فاکتورهای خرید مرتبط:</strong>
                <ul class="mt-2">
                    @foreach($pre_invoice->purchasePreInvoices as $ppi)
                        @php
                            // وضعیت آیتم‌های این پیش‌فاکتور خرید
                            $totalItems        = $ppi->purchaseItems->count();
                            $purchasedItems    = $ppi->purchaseItems
                                ->where('purchase_status', 'purchased')->count();
                            $hasFinalWeight    = $ppi->purchaseItems
                                ->whereNotNull('final_purchase_weight')->count() === $totalItems && $totalItems > 0;

                            if (
                                $totalItems === 0 ||
                                $purchasedItems !== $totalItems ||
                                ! $ppi->supplier_payment_approved
                            ) {
                                $allPurchasesCompleted = false;
                            }

                            // تعیین برچسب وضعیت نمایشی
                            if ($totalItems > 0 && $purchasedItems === $totalItems && $ppi->supplier_payment_approved) {
                                $ppiStatusLabel = 'خرید نهایی شده (آماده بازگشت به فروش)';
                            } elseif ($totalItems > 0 && $purchasedItems === $totalItems) {
                                $ppiStatusLabel = 'خرید آیتم‌ها تکمیل شده (در انتظار تایید پرداخت مالی)';
                            } elseif ($ppi->status === PreInvoiceStatus::WaitingPurchase) {
                                $ppiStatusLabel = 'در انتظار اجرای خرید';
                            } elseif ($ppi->status === PreInvoiceStatus::PricedByPurchase) {
                                $ppiStatusLabel = 'در حال قیمت‌گذاری توسط خرید';
                            } elseif ($ppi->status === PreInvoiceStatus::WaitingFinancePurchase) {
                                $ppiStatusLabel = 'در انتظار تایید مالی قبل از خرید';
                            } elseif ($ppi->status === PreInvoiceStatus::FinancePurchaseApproved) {
                                $ppiStatusLabel = 'مالی تایید کرده (خرید در حال انجام)';
                            } elseif ($ppi->status === PreInvoiceStatus::FinancePurchaseRejected) {
                                $ppiStatusLabel = 'رد شده توسط مالی';
                            } else {
                                $ppiStatusLabel = $ppi->status_label ?? $ppi->status;
                            }
                            
                            $sourceName = null;

                            if (!empty($ppi->source?->name)) {
                                $sourceName = $ppi->source->name;
                            } elseif (!empty($ppi->source?->last_name) || !empty($ppi->source?->first_name)) {
                                $sourceName = trim(($ppi->source->first_name ?? '') . ' ' . ($ppi->source->last_name ?? ''));
                            } else {
                                $sourceName = '-';
                            }
                        @endphp

                        


                        <li class="small mb-1">
                            پیش‌فاکتور خرید #{{ $ppi->id }}
                            – منبع: {{ $sourceName }}
                            – کارشناس خرید: {{ $ppi->buyer?->name ?? '-' }}
                            – وضعیت: {{ $ppiStatusLabel }}

                            {{-- اگر همه آیتم‌ها خرید شده‌اند، یک تگ اضافه هم نشان بده --}}
                            @if($totalItems > 0 && $purchasedItems === $totalItems)
                                <span class="badge bg-success ms-1">
                                    همه آیتم‌ها خریداری شده‌اند
                                </span>
                                @if($hasFinalWeight)
                                    <span class="badge bg-primary ms-1">
                                        وزن نهایی همه آیتم‌ها ثبت شده است
                                    </span>
                                @endif
                            @endif

                            <a href="{{ route('purchase_pre_invoices.purchase_show', $ppi->id) }}"
                            target="_blank"
                            class="btn btn-link btn-sm p-0 align-baseline">
                                مشاهده / اقدام
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
                @php
                    // dd($pre_invoice->status);
                @endphp
                @if($allPurchasesCompleted &&
                    in_array($pre_invoice->status, [
                        PreInvoiceStatus::WaitingPurchaseExecution,
                        PreInvoiceStatus::Purchasing,
                        PreInvoiceStatus::PurchaseCompleted,
                    ]))
                    <form method="POST"
                        action="{{ route('pre_invoices.approve_full_purchase', $pre_invoice->id) }}"
                        class="mt-2">
                        @csrf
                        <button class="btn btn-sm btn-success">
                            تایید خرید کل پیش‌فاکتور و ارجاع به فروش
                        </button>
                    </form>
                @endif
                
        @endif

        
        {{-- ۱. دکمه تایید کارشناس فروش بعد از خرید --}}
        @if($pre_invoice->status === PreInvoiceStatus::PurchaseCompleted)
            <form method="POST"
                action="{{ route('pre_invoices.post_purchase_sales_approve', $pre_invoice->id) }}"
                class="mt-3">
                @csrf
                <button class="btn btn-sm btn-primary">
                    تایید شرایط توسط کارشناس فروش
                </button>
            </form>
        @endif

        {{-- ۲. دکمه درخواست فرم حمل --}}
        @if($pre_invoice->status === PreInvoiceStatus::PostPurchaseSalesApproved)
            <form method="POST"
                action="{{ route('pre_invoices.request_shipping', $pre_invoice->id) }}"
                class="mt-2">
                @csrf
                <button class="btn btn-sm btn-success">
                    درخواست فرم حمل
                </button>
            </form>
        @endif

        @if($pre_invoice->status === \App\Enums\PreInvoiceStatus::ShippingRequested)
            <a href="{{ route('pre_invoices.transports.index', $pre_invoice->id) }}"
            class="btn btn-sm btn-outline-primary mt-2">
                فرم‌های حمل این پیش‌فاکتور / درخواست فرم حمل
            </a>
        @endif

        
        @if($pre_invoice->status === \App\Enums\PreInvoiceStatus::ShippingInProgress)
            <a href="{{ route('pre_invoices.transports.index', $pre_invoice->id) }}"
            class="btn btn-sm btn-outline-primary mt-2">
                در حال حمل قرار گرفته است / نمایش فرم حمل
            </a>

            <form method="POST" action="{{ route('pre_invoices.sales-manager-decision', $pre_invoice) }}">
                @csrf
                @method('PUT')

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="decision" id="wait" value="wait" checked>
                    <label class="form-check-label" for="wait">
                        تا تخلیه کامل بار صبر می‌کنم و فعلاً به فاکتور تبدیل نشود.
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="decision" id="approve_and_invoice" value="approve_and_invoice">
                    <label class="form-check-label" for="approve_and_invoice">
                        پیش‌فاکتور را تأیید می‌کنم و می‌خواهم به فاکتور تبدیل شود.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm mt-3">
                    ثبت تصمیم مدیر فروش
                </button>
            </form>
        @endif

        @if($pre_invoice->status === \App\Enums\PreInvoiceStatus::Delivered)
            <a href="{{ route('pre_invoices.transports.index', $pre_invoice->id) }}"
            class="btn btn-sm btn-outline-primary mt-2">
                تخلیه انجام شده و مورد تأیید مدیر فروش می‌باشد / نمایش فرم حمل
            </a>

            <form method="POST" action="{{ route('pre_invoices.sales-manager-decision', $pre_invoice) }}">
                @csrf
                @method('PUT')

                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="decision" id="approve_and_invoice_delivered" value="approve_and_invoice">
                    <label class="form-check-label" for="approve_and_invoice_delivered">
                        پیش‌فاکتور را تأیید می‌کنم و می‌خواهم به فاکتور تبدیل شود.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm mt-3">
                    ثبت تصمیم مدیر فروش
                </button>
            </form>
        @endif


        </div>

        </div>
    </div>

    {{-- جدول آیتم‌های پیش‌فاکتور (ساده) --}}
    <div class="card">
        <div class="card-header">آیتم‌ها</div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>محصول</th>
                    <th>تعداد</th>
                    <th>قیمت پیشنهادی اولیه</th>
                    <th>قیمت خرید (توسط خرید)</th>
                    <th>قیمت فروش (توسط فروش)</th>
                    <th>حاشیه سود (%)</th>
                    <th>مبلغ نهایی (بر اساس فروش)</th>
                    <th>وضعیت خرید</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pre_invoice->saleItems as $item)
                    @php
                        $buy  = $item->purchase_unit_price;
                        $sale = $item->sale_unit_price ?? $item->unit_price;
                        $total = $item->total;
                        
                        // آیا این آیتم در پیش‌فاکتورهای خرید مرتبط، خرید شده است؟
                        $purchased = false;
                        $finalWeight = null;

                        // dd($pre_invoice->purchasePreInvoices);

                        foreach ($pre_invoice->purchasePreInvoices as $pPre) {
                            foreach ($pPre->purchaseItems as $pItem) {
                                
                                if ($pItem->product_id == $item->product_id && $pItem->purchase_status === 'purchased') {
                                    $purchased  = true;
                                    $finalWeight = $pItem->final_purchase_weight;
                                }
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price) }}</td>
                        <td>{{ $buy ? number_format($buy) : '-' }}</td>
                        <td>{{ $sale ? number_format($sale) : '-' }}</td>
                        <td>
                            @if(!is_null($item->profit_percent))
                                {{ number_format($item->profit_percent, 2) }}
                            @elseif($buy && $sale)
                                {{-- محاسبه لحظه‌ای اگر در DB نباشد --}}
                                {{ number_format((($sale - $buy) / $buy) * 100, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $total ? number_format($total) : '-' }}</td>
                        <td>
                            @if($purchased)
                                <span class="badge bg-success">
                                    خریداری شده (وزن: {{ $finalWeight }})
                                </span>
                            @else
                                <span class="badge bg-warning">در حال خرید</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(in_array($pre_invoice->status, [
        \App\Enums\PreInvoiceStatus::PricedBySales,
        \App\Enums\PreInvoiceStatus::WaitingSalesApproval,
        \App\Enums\PreInvoiceStatus::ApprovedBySalesManager,
        \App\Enums\PreInvoiceStatus::RejectedBySalesManager,
    ]))
        <div class="card mt-3">
            <div class="card-header">خلاصه سود و جمع‌ها</div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <thead>
                    <tr>
                        <th>جمع تعداد</th>
                        <th>جمع مبلغ خرید</th>
                        <th>جمع مبلغ فروش</th>
                        <th>جمع سود (فروش − خرید)</th>
                        <th>درصد سود کل</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ number_format($totals['quantity']) }}</td>
                        <td>{{ number_format($totals['purchase_amount']) }}</td>
                        <td>{{ number_format($totals['sale_amount']) }}</td>
                        <td>{{ number_format($totals['profit_amount']) }}</td>
                        <td>{{ number_format($totals['profit_percent'], 2) }}%</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif



    @section('script')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const card  = document.getElementById('payment-plan-card');
        if (!card) return;

        const total = parseFloat(card.getAttribute('data-pre-invoice-total'));

        const tbody   = document.querySelector('#plan-table tbody');
        const btnAdd  = document.getElementById('add-plan-row');
        const btnSave = document.getElementById('save-plan');
        const sumSpan = document.getElementById('plan-sum');
        const errBox  = document.getElementById('plan-error');
        const okBox   = document.getElementById('plan-success');

        let rowIndex = 0;

        function recalcSum() {
            let sum = getCurrentSum();
            sumSpan.textContent = sum.toLocaleString('fa-IR');
            errBox.style.display = 'none';
            okBox.style.display  = 'none';
            btnSave.disabled = (sum !== total);
        }

        function getCurrentSum() {
            let sum = 0;
            tbody.querySelectorAll('input[name="amount[]"]').forEach(function (inp) {
                const v = parseFloat(inp.value || 0);
                if (!isNaN(v)) sum += v;
            });
            return sum;
        }

        function addRow(amountDefault = 0) {
            rowIndex++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${rowIndex}</td>
                <td>
                    <input type="number" step="0.01" name="amount[]" class="form-control plan-amount"
                        value="${amountDefault || ''}" required>
                </td>
                <td>
                    <input type="date" name="scheduled_date[]" class="form-control" required>
                </td>
                <td>
                    <select name="payment_type[]" class="form-control" required>
                        <option value="cash">نقدی</option>
                        <option value="installment">چکی/اقساط</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger plan-remove">حذف</button>
                </td>
            `;
            tbody.appendChild(tr);

            tr.querySelector('.plan-amount').addEventListener('input', recalcSum);
            tr.querySelector('.plan-remove').addEventListener('click', function () {
                tr.remove();
                rowIndex = 0;
                tbody.querySelectorAll('tr').forEach(function (row) {
                    rowIndex++;
                    row.querySelector('td:first-child').textContent = rowIndex;
                });
                recalcSum();
            });

            recalcSum();
        }

        btnAdd.addEventListener('click', function () {
            const currentSum = getCurrentSum();
            const remaining  = total - currentSum;

            if (remaining <= 0) {
                errBox.textContent = 'مبلغی برای تقسیم روی ردیف جدید باقی نمانده است.';
                errBox.style.display = 'block';
                return;
            }

            addRow(remaining);
        });

        addRow(total);

        btnSave.addEventListener('click', function () {
            errBox.style.display = 'none';
            okBox.style.display  = 'none';

            let sum = 0;
            const rows = [];
            tbody.querySelectorAll('tr').forEach(function (tr) {
                const amount   = parseFloat(tr.querySelector('input[name="amount[]"]').value || 0);
                const date     = tr.querySelector('input[name="scheduled_date[]"]').value;
                const type     = tr.querySelector('select[name="payment_type[]"]').value;
                sum += amount;
                rows.push({amount, scheduled_date: date, payment_type: type});
            });

            if (sum !== total) {
                errBox.textContent = 'جمع اقساط باید دقیقاً برابر مبلغ پیش‌فاکتور باشد.';
                errBox.style.display = 'block';
                btnSave.disabled = true;
                return;
            }

            fetch("{{ route('pre_invoices.payment_plan.store',$pre_invoice->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items: rows })
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    okBox.textContent = 'برنامه پرداخت با موفقیت ثبت شد.';
                    okBox.style.display = 'block';
                } else {
                    errBox.textContent = res.message || 'خطا در ثبت برنامه.';
                    errBox.style.display = 'block';
                }
            })
            .catch(() => {
                errBox.textContent = 'خطای ارتباط با سرور.';
                errBox.style.display = 'block';
            });
        });
    });
    </script>
    @endsection


@endsection

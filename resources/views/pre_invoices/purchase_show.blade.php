@extends('layouts.master')
@section('title','پیش‌فاکتور خرید #'.$preInvoice->id)

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') پیش‌فاکتور خرید #{{ $preInvoice->id }} @endslot
@endcomponent

@php
    $totalPurchase = $preInvoice->items->sum(fn($i) => $i->quantity * $i->unit_price);
    $totalPlanned  = $preInvoice->paymentPlans->sum('amount');
@endphp

@php
    $remaining = $remaining ?? ($preInvoice->total_amount - ($totalPaid ?? 0));
    $totalPaid = $totalPaid ?? 0;
@endphp

@php
    use App\Enums\PreInvoiceStatus;
@endphp

<div class="card mb-3">
    <div class="card-body">
        <p>منبع: {{ $preInvoice->source?->last_name }}</p>
        <p>کارشناس خرید: {{ $preInvoice->buyer?->name }}</p>
        <p>پیش‌فاکتور فروش مرجع:
            @if($preInvoice->salePreInvoice)
                <a href="{{ route('pre-invoices.show', $preInvoice->salePreInvoice) }}" target="_blank">
                    #{{ $preInvoice->salePreInvoice->id }}
                </a>
            @else
                -
            @endif
        </p>
        <p>مبلغ کل: {{ number_format($preInvoice->total_amount) }}</p>
        <p>وضعیت: {{ $preInvoice->status_label ?? $preInvoice->status }}</p>
    </div>
</div>


{{-- فقط وقتی در انتظار تایید مالی است --}}

@php
//dd($preInvoice->status);
@endphp
@if($preInvoice->status === \App\Enums\PreInvoiceStatus::WaitingFinancePurchase)
    <div class="card mt-3">
        <div class="card-header">اقدام مالی روی این پیش‌فاکتور خرید</div>
        <div class="card-body">
            {{-- پیام‌ها --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('finance.pre-invoices.purchase-pre-invoices.approve', $preInvoice) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">
                    تایید مالی خرید
                </button>
            </form>

            <button class="btn btn-danger" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#financeRejectForm">
                رد مالی خرید
            </button>

            <div id="financeRejectForm" class="collapse mt-3">
                <form method="POST" action="{{ route('finance.pre-invoices.purchase-pre-invoices.reject', $preInvoice) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">دلیل رد</label>
                        <textarea name="finance_reject_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger">ثبت رد مالی</button>
                </form>
            </div>
        </div>
    </div>
@endif

@if($preInvoice->status === \App\Enums\PreInvoiceStatus::FinancePurchaseApproved)
    <div class="mt-3">
        @if($preInvoice->salePreInvoice)
            <a href="{{ route('pre-invoices.show', $preInvoice->salePreInvoice) }}"
               class="btn btn-secondary">
                بازگشت به پیش‌فاکتور فروش #{{ $preInvoice->salePreInvoice->id }}
            </a>
        @endif
    </div>
@endif

{{-- اکشن‌های مدیر خرید روی پیش‌فاکتور خرید --}}
@if(in_array($preInvoice->status, [
    \App\Enums\PreInvoiceStatus::Draft,
    \App\Enums\PreInvoiceStatus::ApprovedManager, // اگر نیاز داشتی
]))
    <div class="card mt-3">
        <div class="card-header">اقدام مدیر خرید</div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('purchase-manager.purchase-pre-invoices.approve', $preInvoice) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">
                    تایید مدیر خرید برای این پیش‌فاکتور خرید
                </button>
            </form>

            <button class="btn btn-danger" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#purchaseManagerRejectForm">
                رد این پیش‌فاکتور خرید
            </button>

            <div id="purchaseManagerRejectForm" class="collapse mt-3">
                <form method="POST" action="{{ route('purchase-manager.purchase-pre-invoices.reject', $preInvoice) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">دلیل رد (مدیر خرید)</label>
                        <textarea name="purchase_manager_reject_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger">ثبت رد مدیر خرید</button>
                </form>
            </div>
        </div>
    </div>
@endif


<div class="card">
    <div class="card-header">آیتم‌ها</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد خرید</th>
                <th>مبلغ</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
                @foreach($preInvoice->purchaseItems as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->product?->name }}</td>
                        @php
                            $qtyForTotal = (!is_null($item->final_purchase_weight) && $item->final_purchase_weight > 0)
                                ? $item->final_purchase_weight
                                : $item->final_quantity;
                        @endphp
                        
                        <td>{{ $qtyForTotal }}</td>

                        <td>{{ number_format($item->final_unit_price) }}</td>
                        <td>{{ number_format($qtyForTotal * $item->final_unit_price) }}</td>

                        <td>
                            @if($preInvoice->supplier_payment_approved && $item->purchase_status !== 'purchased')
                               
                                    <form method="POST"
                                        action="{{ route('purchase_pre_invoices.items.finalize_purchase', $item->id) }}"
                                        class="d-flex gap-2">
                                        @csrf
                                        <input type="number" step="0.001" name="final_purchase_weight"
                                            class="form-control form-control-sm"
                                            value="{{ old('final_purchase_weight', $item->final_purchase_weight) }}"
                                            placeholder="وزن نهایی" required>

                                        <button class="btn btn-sm btn-success">
                                            تایید خرید این آیتم
                                        </button>
                                    </form>
                               
                            @else
                                @if($item->purchase_status === 'purchased')
                                    <span class="text-success">
                                        خرید شده (وزن: {{ $item->final_purchase_weight }})
                                    </span>
                                @else
                                    <span class="text-muted">
                                        در انتظار تایید پرداخت به منبع
                                    </span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach


            </tbody>
        </table>
    </div>
</div>

{{-- فقط وقتی خرید قیمت‌گذاری و وزن نهایی شده است اجازه تعریف پلن پرداخت بده --}}
@if($preInvoice->status === \App\Enums\PreInvoiceStatus::PurchaseCompleted 
    || $preInvoice->purchaseItems->every(fn($i) => $i->final_quantity && $i->final_unit_price))
      {{-- پلن پرداخت به منبع --}}
    <div class="card mt-4" id="payment-plan-card"
         data-pre-invoice-total="{{ $preInvoice->total_amount }}">
        <div class="card-header">
            برنامه پرداخت به منبع (جمع: {{ number_format($totalPurchase) }})
        </div>
        <div class="card-body">

            <div class="alert alert-info" id="plan-status">
                مجموع اقساط ثبت‌شده:
                <span id="plan-sum">0</span>
                از {{ number_format($totalPurchase) }}
            </div>

            <table class="table table-bordered align-middle" id="plan-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>مبلغ</th>
                    <th>تاریخ پرداخت</th>
                    <th>نوع پرداخت</th>
                    <th>توضیح</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {{-- در لود اولیه می‌توانی پلن‌های قبلی را هم اینجا رندر کنی --}}
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary mb-2" id="add-plan-row">
                افزودن ردیف
            </button>
            <button type="button" class="btn btn-success mb-2" id="save-plan" disabled>
                ذخیره برنامه پرداخت
            </button>

            <div class="text-danger mt-2" id="plan-error" style="display:none"></div>
            <div class="text-success mt-2" id="plan-success" style="display:none"></div>
        </div>
    </div>

    {{-- اقساط برنامه‌ریزی‌شده برای پیش‌فاکتور --}}
        @if($preInvoice->paymentPlans && $preInvoice->paymentPlans->count())
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
                        @foreach($preInvoice->paymentPlans as $plan)
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

            @if($preInvoice->hasAdvancePaidPendingFinance() && $preInvoice->status !== 'WaitingFinance')
                <form method="POST"
                    action="{{ route('pre_invoices.send_to_finance', $preInvoice->id) }}"
                    class="d-inline">
                    @csrf
                    <button class="btn btn-warning">
                        ارسال به مالی برای تایید پیش‌پرداخت
                    </button>
                </form>
            @endif


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

                fetch("{{ route('pre_invoices.payment_plan.store',$preInvoice->id) }}", {
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

    @endif

    

    {{-- دکمه تایید کلی پیش‌پرداخت --}}
    @if($preInvoice->status === PreInvoiceStatus::AdvanceWaitingFinance)
        <div class="mt-3">

            {{-- دکمه تایید کلی (فقط اگر حداقل یک پرداخت تایید شده باشد) --}}
            <form method="POST"
                action="{{ route('pre_invoices.advance_confirm', $preInvoice->id) }}"
                class="d-inline">
                @csrf
                <button class="btn btn-success"
                        @if(! $preInvoice->hasConfirmedPayments()) disabled @endif>
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
                            $payments = $preInvoice->paymentsForFinance();
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
    @if($preInvoice->status === PreInvoiceStatus::AdvanceFinanceApproved)
        <form method="POST"
            action="{{ route('pre_invoices.go_to_buying', $preInvoice->id) }}"
            class="mt-3">
            @csrf
            <button class="btn btn-primary">
                انتقال به مرحله خرید
            </button>
        </form>
    @endif


{{-- جدول آیتم‌ها با فرم نهایی‌کردن خرید هر آیتم --}}
<table class="table table-bordered mt-3">
    <thead>
    <tr>
        <th>#</th>
        <th>تغییر</th>
        <th>کالا</th>
        <th>مقدار در فروش</th>
        <th>مقدار نهایی خرید</th>
        <th>قیمت واحد نهایی</th>
        <th>جمع نهایی</th>
        <th>عملیات</th>
    </tr>
    </thead>
    <tbody>
    @foreach($preInvoice->purchaseItems as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>
            
            {{-- لیست assignmentهای این آیتم --}}
            @foreach($item->purchaseAssignments as $assignment)
                @if($assignment->pre_invoice_item_id == $item->id && $assignment->buyer_id == $preInvoice->buyer?->id && $assignment->source_id == $preInvoice->source?->id && $assignment->id == $item->chosen_purchase_assignment_id)
                    <div class="mb-2 border rounded p-1">
                        <div>
                            کارشناس: {{ $assignment->buyer?->name ?? '-' }}
                            | منبع: {{ $assignment->source?->last_name ?? '-' }}
                            | قیمت: {{ number_format($assignment->unit_price) }}
                            | وضعیت: {{ $assignment->status }}
                        </div>

                        {{-- فرم تغییر کارشناس (فقط برای نقش‌های مجاز) --}}
                        @if(auth()->user()->hasRole(['purchase_manager','ceo','it','commerce']))
                            <form method="POST"
                                action="{{ route('purchase_assignments.change_buyer', $assignment->id) }}"
                                class="d-inline">
                                @csrf
                                <select name="buyer_id" class="form-select form-select-sm d-inline-block w-auto">
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}"
                                            @selected($assignment->buyer_id == $buyer->id)>
                                            {{ $buyer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-primary">تغییر کارشناس</button>
                            </form>
                        @endif

                        {{-- فرم تغییر منبع --}}
                        @php $user = auth()->user(); @endphp
                        @if(
                            $user->hasRole(['purchase_manager','ceo','it','commerce'])
                            || ($user->hasRole('purchase_buyer') && $assignment->buyer_id === $user->id)
                        )
                            <form method="POST"
                                action="{{ route('purchase_assignments.change_source', $assignment->id) }}"
                                class="d-inline ms-2">
                                @csrf
                                <select name="source_id" class="form-select form-select-sm d-inline-block w-auto">
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}"
                                            @selected($assignment->source_id == $source->id)>
                                            {{ $source->first_name }} {{ $source->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-secondary">تغییر منبع</button>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach

            
            </td>
            <td>{{ $item->product?->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->final_quantity ?? '-' }}</td>
            <td>{{ $item->final_unit_price ? number_format($item->final_unit_price) : '-' }}</td>
            <td>{{ $item->final_total_price ? number_format($item->final_total_price) : '-' }}</td>
            <td>
                @if($preInvoice->status === \App\Enums\PreInvoiceStatus::FinancePurchaseApproved)
                    <form method="POST" action="{{ route('purchase_pre_invoices.items.finalize', $item->id) }}" class="row g-1">
                        @csrf
                        <div class="col-4">
                            <input type="number" step="0.001" name="final_quantity"
                                class="form-control form-control-sm"
                                value="{{ old('final_quantity', $item->final_quantity ?? $item->quantity) }}"
                                placeholder="وزن/مقدار نهایی">
                        </div>
                        <div class="col-4">
                            <input type="number" step="0.01" name="final_unit_price"
                                class="form-control form-control-sm"
                                value="{{ old('final_unit_price', $item->final_unit_price ?? $item->unit_price) }}"
                                placeholder="قیمت واحد نهایی">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-sm btn-success w-100">
                                ثبت خرید آیتم
                            </button>
                        </div>
                    </form>
                @endif    
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- دکمه تایید خرید کل، مخصوص مدیر خرید --}}
@can('purchase-manager') {{-- هر شرط دسترسی که داری --}}
    <form method="POST" action="{{ route('purchase_pre_invoices.approve_purchase', $preInvoice->id) }}">
        @csrf
        <button class="btn btn-primary mt-3">
            تایید خرید و ارسال به فروش
        </button>
    </form>
@endcan


@endsection

@php
    $remaining = $remaining ?? ($pre_invoice->total_amount - ($totalPaid ?? 0));
    $totalPaid = $totalPaid ?? 0;
@endphp

@extends('layouts.master')
@section('title','پیش‌فاکتور #'.$pre_invoice->id)

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>اطلاعات پیش‌فاکتور</span>
        <span class="badge bg-info">{{ $pre_invoice->status_label }}</span>
    </div>
    @if($pre_invoice->isReadyForCustomer())
        <a href="{{ route('pre_invoices.print',$pre_invoice) }}"
        class="btn btn-outline-secondary btn-sm">
            چاپ پیش‌فاکتور
        </a>
    @endif

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-2">{{ session('error') }}</div>
        @endif

        <p>مشتری:
            {{ $pre_invoice->customer?->first_name }}
            {{ $pre_invoice->customer?->last_name }}
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

        {{-- خلاصه وضعیت پیش‌فاکتورهای خرید --}}
        @if($pre_invoice->hasActivePurchasePreInvoices())
            <div class="mt-2">
                <strong>پیش‌فاکتورهای خرید مرتبط (در حال بررسی):</strong>
                @foreach($pre_invoice->purchasePreInvoices as $ppi)
                    @if(!in_array($ppi->status, [
                        \App\Enums\PreInvoiceStatus::FinancePurchaseApproved,
                        \App\Enums\PreInvoiceStatus::FinancePurchaseRejected,
                    ]))
                        <div class="small">
                            #{{ $ppi->id }} - {{ $ppi->source?->name }} / {{ $ppi->buyer?->name }}
                            - وضعیت: {{ $ppi->status_label ?? $ppi->status }}
                            <a href="{{ route('purchase-pre-invoices.show', $ppi) }}"
                            class="btn btn-link btn-sm p-0 align-baseline">
                                مشاهده / اقدام
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif



        {{-- 5 → priced_by_purchase: ثبت قیمت‌های خرید (کارشناسان خرید) --}}
        @if($pre_invoice->canBePricedByPurchase())
            <form method="POST" action="{{ route('pre_invoices.price_by_purchase',$pre_invoice) }}">
                @csrf
                <button class="btn btn-primary btn-sm">
                    ثبت قیمت‌های خرید
                </button>
            </form>

            {{-- لینک بررسی برای کارشناس خرید / مدیر خرید --}}
            <a href="{{ route('purchase-manager.pre-invoices.review', $pre_invoice->id) }}"
            class="btn btn-outline-primary btn-sm">
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
        @if($pre_invoice->canBePricedBySales())
            <form method="POST" action="{{ route('pre_invoices.price_by_sales',$pre_invoice) }}" class="d-inline">
                @csrf
                <button class="btn btn-primary btn-sm">
                    ثبت قیمت‌های فروش (تایید نهایی)
                </button>
            </form>

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
            </tr>
            </thead>
            <tbody>
            @foreach($pre_invoice->saleItems as $item)
                @php
                    $buy  = $item->purchase_unit_price;
                    $sale = $item->sale_unit_price ?? $item->unit_price;
                    $total = $item->total;
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

@extends('layouts.master')
@section('title','فاکتور #'.$invoice->id)

@php
    $remaining = $invoice->remaining_amount;   // مانده پرداخت
    $totalPaid = $invoice->paid_amount;        // مجموع مبالغ پرداخت‌شده تاییدشده
@endphp

@section('content')
<div class="card mb-3">
    <div class="card-header">اطلاعات فاکتور</div>
    <div class="card-body">
        <p>مشتری: {{ $invoice->customer?->first_name }} {{ $invoice->customer?->last_name }}</p>
        <p>مبلغ کل: {{ number_format($invoice->total_amount) }}</p>
        @if($invoice->formal_extra)
            <p>افزوده رسمی: {{ number_format($invoice->formal_extra) }}</p>
        @endif
        <p>مبلغ پرداخت‌شده تا الان: {{ number_format($totalPaid) }}</p>
        <p>مانده پرداخت: {{ number_format($remaining) }}</p>
        <p>وضعیت فاکتور: {{ $invoice->status }}</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">آیتم‌ها</div>
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد</th>
                <th>مبلغ</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price) }}</td>
                    <td>{{ number_format($item->total) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- برنامه داینامیک پرداخت (فقط سمت کارشناس برنامه‌ریز) --}}
@if($remaining > 0)
<div class="card mt-4" id="payment-plan-card"
     data-invoice-total="{{ $invoice->total_amount }}">
    <div class="card-header">
        برنامه پرداخت (مبلغ فاکتور: {{ number_format($invoice->total_amount) }})
    </div>
    <div class="card-body">
        <div class="alert alert-info" id="plan-status">
            جمع اقساط: <span id="plan-sum">0</span> از {{ number_format($invoice->total_amount) }} ریال
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
            <!-- ردیف‌ها به صورت داینامیک با JS اضافه می‌شوند -->
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

{{-- جدول پرداخت‌های ثبت‌شده (برنامه‌ریزی‌شده و واقعی) --}}
@if($invoice->plans->count())
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
            @foreach($invoice->plans as $plan)
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
                            <form method="POST" action="{{ route('plans.pay',$plan->id) }}"
                                  enctype="multipart/form-data" class="d-flex gap-1">
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
                                <button class="btn btn-sm btn-success">ثبت پرداخت</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif


@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const card  = document.getElementById('payment-plan-card');
    if (!card) return;

    const total = parseFloat(card.getAttribute('data-invoice-total'));

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

        if (sum === total) {
            btnSave.disabled = false;
        } else {
            btnSave.disabled = true;
        }
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

        // اگر چیزی برای تقسیم باقی نمانده، ردیف جدید نیاور
        if (remaining <= 0) {
            errBox.textContent = 'مبلغی برای تقسیم روی ردیف جدید باقی نمانده است.';
            errBox.style.display = 'block';
            return;
        }

        // ردیف جدید با مقدار باقی‌مانده
        addRow(remaining);
    });


    // شروع: یک ردیف با کل مبلغ
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
            errBox.textContent = 'جمع اقساط باید دقیقاً برابر مبلغ فاکتور باشد.';
            errBox.style.display = 'block';
            btnSave.disabled = true;
            return;
        }

        fetch("{{ route('invoices.payment-plan.store',$invoice->id) }}", {
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

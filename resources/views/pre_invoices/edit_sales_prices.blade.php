@extends('layouts.master')
@section('title','فرم قیمت‌گذاری فروش - پیش‌فاکتور #'.$pre_invoice->id)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>فرم قیمت‌گذاری فروش</span>
        <span class="badge bg-info">پیش‌فاکتور #{{ $pre_invoice->id }}</span>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mb-2">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="mb-1">
            مشتری:
            {{ $pre_invoice->customer?->first_name }}
            {{ $pre_invoice->customer?->last_name }}
        </p>
        <p class="mb-3">
            منبع تأمین پیشنهادی:
            {{ $pre_invoice->source?->name ?? '-' }}
        </p>

        <form method="POST" action="{{ route('pre_invoices.save_sale_prices', $pre_invoice) }}">
            @csrf

            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>قیمت خرید (تومان)</th>
                        <th>حاشیه سود (%)</th>
                        <th>قیمت واحد فروش (تومان)</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pre_invoice->items as $index => $item)
                    @php
                        $buyPrice   = $item->purchase_unit_price ?? 0;
                        $salePrice  = old("items.{$item->id}.sale_unit_price", $item->unit_price);
                        $profitPerc = old("items.{$item->id}.profit_percent", $item->profit_percent ?? null);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            {{ number_format($buyPrice) }}
                            <input type="hidden"
                                class="buy-price"
                                value="{{ $buyPrice }}">
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                name="items[{{ $item->id }}][profit_percent]"
                                class="form-control form-control-sm profit-input"
                                value="{{ $profitPerc }}"
                                data-row="{{ $item->id }}">
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                name="items[{{ $item->id }}][sale_unit_price]"
                                class="form-control form-control-sm sale-input"
                                value="{{ $salePrice }}"
                                data-row="{{ $item->id }}">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>


            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    ذخیره قیمت‌های فروش
                </button>
                <a href="{{ route('pre-invoices.show',$pre_invoice) }}" class="btn btn-secondary">
                    بازگشت به پیش‌فاکتور
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // وقتی درصد سود عوض می‌شود → قیمت فروش را محاسبه کن
    document.querySelectorAll('.profit-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const rowId     = this.dataset.row;
            const row       = this.closest('tr');
            const buyPrice  = parseFloat(row.querySelector('.buy-price').value) || 0;
            const percent   = parseFloat(this.value) || 0;
            const saleInput = row.querySelector('.sale-input');

            if (buyPrice > 0) {
                const salePrice = buyPrice * (1 + percent / 100);
                saleInput.value = salePrice.toFixed(2);
            }
        });
    });

    // وقتی قیمت فروش عوض می‌شود → درصد سود را محاسبه کن
    document.querySelectorAll('.sale-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const row       = this.closest('tr');
            const buyPrice  = parseFloat(row.querySelector('.buy-price').value) || 0;
            const salePrice = parseFloat(this.value) || 0;
            const profitInp = row.querySelector('.profit-input');

            if (buyPrice > 0) {
                const percent = ((salePrice - buyPrice) / buyPrice) * 100;
                profitInp.value = percent.toFixed(2);
            }
        });
    });
});
</script>
@endsection


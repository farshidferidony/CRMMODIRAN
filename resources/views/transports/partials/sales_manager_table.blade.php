{{-- resources/views/transports/partials/sales_manager_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
        <thead>
        <tr>
            <th>#</th>
            <th>نوع</th>
            <th>مشخصات وسیله</th>
            <th>وضعیت حمل</th>
            <th>هزینه حمل</th>
            <th>هزینه بارگیری</th>
            <th>هزینه برگشتی</th>
            <th>ارزش کالا</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalGoodsValue = 0;
        @endphp

        @foreach($transport->vehicles as $i => $vehicle)
            @php
                $goodsValue = $vehicle->items->sum(function ($item) {
                    // فرض: در item قیمت واحد یا ارزش کل را داری
                    return $item->goods_value ?? 0;
                });
                $totalGoodsValue += $goodsValue;
            @endphp

            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    @if($vehicle->is_wagon)
                        واگن
                    @else
                        ماشین
                    @endif
                </td>
                <td>
                    @if($vehicle->is_wagon)
                        {{ $vehicle->freight_company_name }}
                    @else
                        {{ $vehicle->driver_name }}
                        <br>
                        <small class="text-muted">{{ $vehicle->freight_company_name }}</small>
                    @endif
                </td>
                <td>
                    <span class="badge bg-light text-dark">
                        {{ $vehicle->status->label() ?? $vehicle->status->value }}
                    </span>
                </td>
                <td>
                    @if($vehicle->is_wagon)
                        {{ number_format($vehicle->wagon_cost) }} ریال
                    @else
                        {{ number_format($vehicle->total_freight_amount) }} ریال
                    @endif
                </td>
                <td>{{ number_format($vehicle->loading_cost ?? 0) }} ریال</td>
                <td>{{ number_format($vehicle->return_amount ?? 0) }} ریال</td>
                <td>{{ number_format($goodsValue) }} ریال</td>
            </tr>
        @endforeach

        <tr class="table-secondary">
            <td colspan="7" class="text-end fw-bold">ارزش کل کالا:</td>
            <td class="fw-bold">{{ number_format($totalGoodsValue) }} ریال</td>
        </tr>
        </tbody>
    </table>
</div>

<form method="POST" action="{{ route('transports.sales-manager.approve', $transport) }}" class="mt-3">
    @csrf
    @method('PUT')

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="approved_by_sales_manager" value="1" id="approved_by_sales_manager" required>
        <label class="form-check-label" for="approved_by_sales_manager">
            تأیید می‌کنم وضعیت حمل، هزینه‌ها و ارزش کالا بررسی و تأیید شده است.
        </label>
    </div>

    <button type="submit" class="btn btn-success btn-sm mt-2">
        ثبت تأیید مدیر فروش
    </button>
</form>

@if($transport->approved_by_sales_manager && $transport->preInvoice)
    <div class="mt-3">
        <a href="{{ route('pre-invoices.show', $transport->preInvoice) }}" class="btn btn-outline-primary btn-sm">
            مشاهده پیش‌فاکتور و ادامه مراحل فروش
        </a>
    </div>
@endif


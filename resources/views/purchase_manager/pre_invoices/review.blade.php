@extends('layouts.master')
@section('title','بازبینی قیمت‌های خرید پیش‌فاکتور')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') بازبینی پیش‌فاکتور فروش #{{ $preInvoice->id }} @endslot
@endcomponent

<div class="card">
    <div class="card-header">
        انتخاب قیمت نهایی خرید برای هر آیتم
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('purchase-manager.pre-invoices.choose-prices',$preInvoice->id) }}">
            @csrf

            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>آیتم</th>
                    <th>محصول</th>
                    <th>تعداد</th>
                    <th>گزینه‌های قیمت خرید (کارشناس/منبع/قیمت)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($preInvoice->items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            @forelse($item->purchaseAssignments as $pa)
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="choices[{{ $item->id }}]"
                                           id="choice_{{ $item->id }}_{{ $pa->id }}"
                                           value="{{ $pa->id }}"
                                           @if($item->chosen_purchase_assignment_id == $pa->id) checked @endif>
                                    <label class="form-check-label" for="choice_{{ $item->id }}_{{ $pa->id }}">
                                        {{ $pa->buyer?->name }}
                                        @if($pa->source) - {{ $pa->source->last_name }} @endif
                                        @if($pa->unit_price)
                                            | {{ number_format($pa->unit_price) }}
                                        @endif
                                        | وضعیت: {{ $pa->status }}
                                    </label>
                                </div>
                            @empty
                                <em>هنوز ارجاع/قیمتی ثبت نشده.</em>
                            @endforelse

                            @if($item->purchase_unit_price)
                                <div class="mt-1 small text-success">
                                    قیمت انتخاب‌شده فعلی: {{ number_format($item->purchase_unit_price) }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">
                ثبت قیمت‌های نهایی خرید و ارسال به فروش
            </button>
        </form>
    </div>
</div>
@endsection

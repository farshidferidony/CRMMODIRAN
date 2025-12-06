@extends('layouts.master')
@section('title','قیمت‌گذاری ارجاع خرید')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') خرید @endslot
    @slot('title') قیمت‌گذاری آیتم @endslot
@endcomponent

<div class="card">
    <div class="card-header">
        آیتم #{{ $assignment->id }} برای پیش‌فاکتور فروش #{{ $assignment->item->preInvoice->id }}
    </div>
    <div class="card-body">
        <p>
            محصول: <strong>{{ $assignment->item->product?->name }}</strong><br>
            تعداد: <strong>{{ $assignment->item->quantity }}</strong>
        </p>
        <p>کارشناس خرید: {{ $assignment->buyer?->name ?? '-' }}</p>

        <form method="POST" action="{{ route('buyer.assignments.update',$assignment->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">منبع</label>
                <select name="source_id" class="form-control">
                    <option value="">بدون تغییر / انتخاب نشده</option>
                    @foreach($sources as $s)
                        <option value="{{ $s->id }}"
                            @if(old('source_id',$assignment->source_id)==$s->id) selected @endif>
                            {{ $s->first_name }} {{ $s->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">قیمت خرید پیشنهادی (به ازای واحد)</label>
                <input type="number" step="0.01" name="unit_price" class="form-control"
                       value="{{ old('unit_price',$assignment->unit_price) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">توضیح (اختیاری)</label>
                <textarea name="note" class="form-control" rows="3">{{ old('note',$assignment->note) }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="confirm" id="confirm"
                       value="1">
                <label class="form-check-label" for="confirm">
                    قیمت پیشنهادی مورد تأیید من است
                </label>
            </div>

            <button type="submit" class="btn btn-success">ذخیره قیمت</button>
            <a href="{{ route('buyer.assignments.index') }}" class="btn btn-secondary">بازگشت</a>
        </form>
    </div>
</div>
@endsection

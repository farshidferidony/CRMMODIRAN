@extends('layouts.master')
@section('title','ویرایش پیش‌فاکتور')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') فروش @endslot
    @slot('title') پیش‌فاکتور #{{ $preInvoice->id }} @endslot
@endcomponent

@php
    $products = \App\Models\Product::orderBy('name')->get();
@endphp

<div class="row">
    <div class="col-lg-8">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header">اطلاعات کلی</div>
            <div class="card-body">
                <form method="POST" action="{{ route('pre-invoices.update',$preInvoice->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">مشتری</label>
                        <select name="customer_id" class="form-control" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}"
                                    @if(old('customer_id',$preInvoice->customer_id)==$c->id) selected @endif>
                                    {{ $c->first_name }} {{ $c->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">منبع</label>
                        <select name="source_id" class="form-control">
                            <option value="">-</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}"
                                    @if(old('source_id',$preInvoice->source_id)==$s->id) selected @endif>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">نوع پیش‌فاکتور</label>
                        <select name="type" class="form-control" required>
                            <option value="normal" @if($preInvoice->type=='normal') selected @endif>عادی</option>
                            <option value="formal" @if($preInvoice->type=='formal') selected @endif>رسمی</option>
                            <option value="export" @if($preInvoice->type=='export') selected @endif>صادراتی</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">ذخیره اطلاعات</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>آیتم‌ها</span>
            </div>
            <div class="card-body">

                {{-- فرم افزودن آیتم --}}
                <form method="POST" action="{{ route('pre-invoices.items.store',$preInvoice->id) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-5">
                        <select name="product_id" class="form-control" required>
                            <option value="">انتخاب محصول...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="quantity" class="form-control" min="1" placeholder="تعداد" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="قیمت واحد" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">افزودن آیتم</button>
                    </div>
                </form>

                {{-- ارجاع آیتم‌ها به کارشناسان خرید --}}
                <p class="mb-2">ارجاع آیتم‌ها به کارشناسان خرید:</p>

                <table class="table table-bordered align-middle mb-4">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>ارجاع به کارشناس خرید</th>
                        <th>ارجاعات موجود</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($preInvoice->items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <form method="POST" action="{{ route('purchase-assignments.store') }}" class="row g-1">
                                    @csrf
                                    <input type="hidden" name="pre_invoice_item_id" value="{{ $item->id }}">

                                    <div class="col-6">
                                        <select name="buyer_id" class="form-control" required>
                                            <option value="">کارشناس خرید...</option>
                                            @foreach($buyers as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select name="source_id" class="form-control">
                                            <option value="">منبع (اختیاری)</option>
                                            @foreach($sources as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mt-1">
                                        <button class="btn btn-sm btn-outline-primary w-100">ارجاع</button>
                                    </div>
                                </form>
                            </td>
                            <td>
                                <ul class="mb-0">
                                    @foreach($item->purchaseAssignments as $pa)
                                        <li>
                                            {{ $pa->buyer?->name }}
                                            @if($pa->source) - {{ $pa->source->name }} @endif
                                            ({{ $pa->status }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{-- جدول آیتم‌ها (برای نمایش قیمت واحد/مبلغ و حذف) --}}
                <table class="table table-bordered align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($preInvoice->items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <form method="POST"
                                      action="{{ route('pre-invoice-items.destroy',$item->id) }}"
                                      onsubmit="return confirm('حذف آیتم؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>جمع کل: {{ number_format($preInvoice->total_amount) }}</strong>
                    @if($preInvoice->formal_extra)
                        <div>افزوده رسمی: {{ number_format($preInvoice->formal_extra) }}</div>
                    @endif
                </div>
            </div>
        </div>
        {{-- فرم قیمت‌های فروش بر اساس قیمت خرید نهایی --}}
        @if(in_array($preInvoice->status, ['priced_by_purchase','priced_by_sales']))
            <div class="card mt-3">
                <div class="card-header">قیمت‌گذاری فروش بر اساس قیمت خرید نهایی</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pre-invoices.save-sale-prices',$preInvoice->id) }}">
                        @csrf

                        <table class="table table-bordered align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>محصول</th>
                                <th>تعداد</th>
                                <th>قیمت خرید نهایی</th>
                                <th>قیمت فروش</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($preInvoice->items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->product?->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        @if($item->purchase_unit_price)
                                            {{ number_format($item->purchase_unit_price) }}
                                        @else
                                            <span class="text-muted">هنوز توسط خرید نهایی نشده</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($preInvoice->status === 'priced_by_purchase' || $preInvoice->status === 'priced_by_sales')
                                            <input type="number" step="0.01"
                                                   name="sale_prices[{{ $item->id }}]"
                                                   class="form-control"
                                                   value="{{ old('sale_prices.'.$item->id, $item->sale_unit_price) }}"
                                                   @if(!$item->purchase_unit_price) disabled @endif>
                                        @else
                                            @if($item->sale_unit_price)
                                                {{ number_format($item->sale_unit_price) }}
                                            @else
                            <span class="text-muted">در انتظار مرحله قبل</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-success mt-2">
                            ذخیره قیمت‌های فروش و ارسال برای تأیید
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if($preInvoice->status === 'priced_by_sales')
            <form method="POST" action="{{ route('pre-invoices.send-to-sales-approval',$preInvoice->id) }}" class="mt-3">
                @csrf
                <button class="btn btn-primary">
                    ارسال برای تأیید مدیر فروش
                </button>
            </form>
        @endif


        @if($preInvoice->status === 'waiting_sales_approval')
            <form method="POST" action="{{ route('pre-invoices.sales-approve',$preInvoice->id) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">تأیید مدیر فروش</button>
            </form>

            <form method="POST" action="{{ route('pre-invoices.sales-reject',$preInvoice->id) }}" class="d-inline ms-2">
                @csrf
                <button class="btn btn-danger">رد پیش‌فاکتور</button>
            </form>
        @endif

        

    </div> {{-- .col-lg-8 --}}
</div> {{-- .row --}}
@endsection

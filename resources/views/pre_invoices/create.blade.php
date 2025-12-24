@extends('layouts.master')
@section('title')
پیش‌فاکتور جدید
@endsection

@section('css')
<link href="{{ URL::asset('/assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection


@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') فروش @endslot
    @slot('title') ایجاد پیش‌فاکتور @endslot
@endcomponent

<div class="row">
    <div class="col-lg-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">اطلاعات کلی پیش‌فاکتور</div>
            <div class="card-body">
                <form method="POST" action="{{ route('pre-invoices.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">مشتری</label>
                        <select data-toggle="select2" id="customer_selector" name="customer_id" class="form-control" required></select>
                    </div>

                    <div class="mb-3 d-none" id="customer_target_wrapper">
                        <label class="form-label">صدور پیش‌فاکتور برای</label>
                        <select id="customer_target" name="customer_target" class="form-control"></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">نوع پیش‌فاکتور</label>
                        <select name="type" class="form-control" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="normal" @if(old('type')=='normal') selected @endif>عادی</option>
                            <option value="formal" @if(old('type')=='formal') selected @endif>رسمی</option>
                            <option value="export" @if(old('type')=='export') selected @endif>صادراتی</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        ثبت و رفتن به افزودن آیتم‌ها
                    </button>
                    <a href="{{ route('pre-invoices.index') }}" class="btn btn-secondary">بازگشت</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection



@section('script')

    <script src="{{ URL::asset('/assets/libs/select2/select2.min.js') }}"></script>
    <script>
        $(function () {
            $('#customer_selector').select2({
                placeholder: 'نام مشتری، شرکت یا کد ملی را وارد کنید...',
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route('ajax.customers.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                theme: 'bootstrap-5', // با تم Minible هماهنگ است
                dir: 'rtl',
                width: '100%'
            });

            // وقتی مشتری انتخاب شد، باید target را مشخص کنیم
            $('#customer_selector').on('select2:select', function (e) {
                const data = e.params.data;

                const person    = data.person || null;
                const companies = data.companies || [];

                const $wrapper  = $('#customer_target_wrapper');
                const $target   = $('#customer_target');

                $target.empty();

                if (!person && companies.length === 0) {
                    $wrapper.addClass('d-none');
                    return;
                }

                // گزینه «برای شخص» اگر شخصی وجود دارد
                if (person) {
                    $target.append(
                        new Option('برای شخص: ' + person.name, 'person:' + person.id, false, false)
                    );
                }

                // اگر شخص در شرکت‌ها کار می‌کند
                if (companies.length === 1) {
                    const c = companies[0];
                    $target.append(
                        new Option('برای شرکت: ' + c.name, 'company:' + c.id, false, false)
                    );
                } else if (companies.length > 1) {
                    companies.forEach(function (c) {
                        $target.append(
                            new Option('برای شرکت: ' + c.name, 'company:' + c.id, false, false)
                        );
                    });
                }

                $wrapper.removeClass('d-none');
            });
        });
    </script>
@endsection

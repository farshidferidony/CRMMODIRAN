@extends('layouts.master')
@section('title','محصولات')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span>لیست محصولات</span>
    <a href="{{ route('products.create') }}" class="btn btn-success btn-sm">محصول جدید</a>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- فرم فیلتر --}}
    <form method="GET" action="{{ route('products.index') }}" class="mb-3">
      <div class="row">
        <div class="col-md-3 mb-2">
          <select name="category_id" id="filter-category" class="form-control">
            <option value="">- دسته -</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @if(request('category_id')==$cat->id) selected @endif>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <input type="number" name="price_min" class="form-control"
                placeholder="حداقل قیمت" value="{{ request('price_min') }}">
        </div>
        <div class="col-md-2 mb-2">
          <input type="number" name="price_max" class="form-control"
                placeholder="حداکثر قیمت" value="{{ request('price_max') }}">
        </div>
        <div class="col-md-3 mb-2">
          <input type="text" name="q" class="form-control"
                placeholder="نام محصول" value="{{ request('q') }}">
        </div>
        <div class="col-md-2 mb-2">
          <button class="btn btn-primary w-100">فیلتر</button>
        </div>
      </div>

      {{-- فیلترهای داینامیک بر اساس attributeهای دسته انتخاب‌شده --}}
      @if($selectedCategory && $filterAttributes->count())
        <hr class="my-2">
        <div class="row">
          @foreach($filterAttributes as $attr)
            <div class="col-md-3 mb-2">
              <label class="form-label">{{ $attr->name }}</label>
              @php
                $fieldName = "attr[{$attr->id}]";
                $oldVal = request('attr')[$attr->id] ?? '';
              @endphp

              @if($attr->type === 'select' && $attr->values)
                @php
                  $options = collect(explode(',', $attr->values))
                              ->map(fn($v) => trim($v))
                              ->filter();
                @endphp
                <select name="{{ $fieldName }}" class="form-control">
                  <option value="">همه</option>
                  @foreach($options as $opt)
                    <option value="{{ $opt }}" @if($oldVal == $opt) selected @endif>
                      {{ $opt }}
                    </option>
                  @endforeach
                </select>
              @else
                {{-- برای text / number فیلتر ساده --}}
                <input type="text" name="{{ $fieldName }}" class="form-control"
                      value="{{ $oldVal }}" placeholder="فیلتر بر اساس {{ $attr->name }}">
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </form>


    {{-- جدول محصولات --}}
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>نام</th>
          <th>دسته</th>
          <th>قیمت</th>
          <th>موجودی</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $p)
        <tr>
          <td>{{ $p->id }}</td>
          <td>{{ $p->name }}</td>
          <td>{{ $p->category?->name }}</td>
          <td>{{ number_format($p->price) }}</td>
          <td>{{ $p->stock }}</td>
          <td>
            <a href="{{ route('products.edit',$p) }}" class="btn btn-warning btn-sm">ویرایش</a>
            <form action="{{ route('products.destroy',$p) }}" method="POST" style="display:inline;">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('حذف محصول؟')">حذف</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{ $products->links() }}
  </div>
</div>
@endsection

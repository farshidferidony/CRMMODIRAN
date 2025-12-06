@extends('layouts.master')
@section('title','ایجاد محصول')

@section('content')
<div class="card">
  <div class="card-header">ایجاد محصول جدید</div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" id="product-form">
      @csrf
      <div class="mb-3">
        <label class="form-label">نام محصول</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name') }}" required>
      </div>

      <div class="mb-3">
        <label class="form-label">دسته</label>
        <select name="category_id" id="category_id" class="form-control" required>
          <option value="">انتخاب کنید</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @if(old('category_id')==$cat->id) selected @endif>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">توضیحات</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">قیمت</label>
          <input type="number" step="0.01" name="price" class="form-control"
                 value="{{ old('price',0) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">موجودی</label>
          <input type="number" name="stock" class="form-control"
                 value="{{ old('stock',0) }}">
        </div>
      </div>

      <hr>
      <h5 class="mb-2">ویژگی‌های محصول (بر اساس دسته)</h5>
      <div id="attributes-wrapper">
        {{-- با JS پر می‌شود --}}
      </div>

      <button type="submit" class="btn btn-success mt-3">ذخیره</button>
      <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">بازگشت</a>
    </form>
  </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('category_id').addEventListener('change', function() {
    let catId = this.value;
    let wrapper = document.getElementById('attributes-wrapper');
    wrapper.innerHTML = '';

    if(!catId) return;

    fetch('{{ url('categories') }}/' + catId + '/attributes')
        .then(r => r.json())
        .then(attrs => {
            if(!attrs.length){
                wrapper.innerHTML = '<p class="text-muted">برای این دسته هیچ ویژگی تعریف نشده.</p>';
                return;
            }
            attrs.forEach(function(attr){
                let html = '';
                html += '<div class="mb-2">';
                html += '<label class="form-label">'+ attr.name +'</label>';

                if(attr.type === 'text'){
                    html += `<input type="text" name="attributes[${attr.id}]" class="form-control">`;
                } else if(attr.type === 'number'){
                    html += `<input type="number" step="0.01" name="attributes[${attr.id}]" class="form-control">`;
                } else if(attr.type === 'select'){
                    let opts = '';
                    if(attr.values){
                        attr.values.split(',').forEach(function(v){
                            let val = v.trim();
                            if(!val) return;
                            opts += `<option value="${val}">${val}</option>`;
                        });
                    }
                    html += `<select name="attributes[${attr.id}]" class="form-control">
                               <option value="">انتخاب کنید</option>${opts}</select>`;
                }

                html += '</div>';
                wrapper.insertAdjacentHTML('beforeend', html);
            });
        });
});
</script>
@endsection

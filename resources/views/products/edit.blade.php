@extends('layouts.master')
@section('title','ویرایش محصول')

@section('content')
<div class="card">
  <div class="card-header">ویرایش محصول</div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('products.update',$product->id) }}" id="product-form">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label">نام محصول</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name',$product->name) }}" required>
      </div>

      <div class="mb-3">
        <label class="form-label">دسته</label>
        <select name="category_id" id="category_id" class="form-control" required>
          <option value="">انتخاب کنید</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}"
                @if(old('category_id',$product->category_id)==$cat->id) selected @endif>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">توضیحات</label>
        <textarea name="description" class="form-control" rows="3">
          {{ old('description',$product->description) }}
        </textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">قیمت</label>
          <input type="number" step="0.01" name="price" class="form-control"
                 value="{{ old('price',$product->price) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">موجودی</label>
          <input type="number" name="stock" class="form-control"
                 value="{{ old('stock',$product->stock) }}">
        </div>
      </div>

      <hr>
      <h5 class="mb-2">ویژگی‌های محصول (بر اساس دسته)</h5>
      <div id="attributes-wrapper"></div>

      <button type="submit" class="btn btn-success mt-3">ذخیره تغییرات</button>
      <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">بازگشت</a>
    </form>
  </div>
</div>
@endsection

@section('script')
<script>
const existingValues = @json($attributeValues ?? []);

function loadAttributes(categoryId){
    let wrapper = document.getElementById('attributes-wrapper');
    wrapper.innerHTML = '';
    if(!categoryId) return;

    fetch('{{ url('categories') }}/' + categoryId + '/attributes')
        .then(r => r.json())
        .then(attrs => {
            if(!attrs.length){
                wrapper.innerHTML = '<p class="text-muted">برای این دسته هیچ ویژگی تعریف نشده.</p>';
                return;
            }
            attrs.forEach(function(attr){
                let html = '';
                let val = existingValues[attr.id] ?? '';

                html += '<div class="mb-2">';
                html += '<label class="form-label">'+ attr.name +'</label>';

                if(attr.type === 'text'){
                    html += `<input type="text" name="attributes[${attr.id}]" class="form-control" value="${val}">`;
                } else if(attr.type === 'number'){
                    html += `<input type="number" step="0.01" name="attributes[${attr.id}]" class="form-control" value="${val}">`;
                } else if(attr.type === 'select'){
                    let opts = '<option value="">انتخاب کنید</option>';
                    if(attr.values){
                        attr.values.split(',').forEach(function(v){
                            let optVal = v.trim();
                            if(!optVal) return;
                            let sel = (optVal === val) ? 'selected' : '';
                            opts += `<option value="${optVal}" ${sel}>${optVal}</option>`;
                        });
                    }
                    html += `<select name="attributes[${attr.id}]" class="form-control">${opts}</select>`;
                }

                html += '</div>';
                wrapper.insertAdjacentHTML('beforeend', html);
            });
        });
}

document.getElementById('category_id').addEventListener('change', function(){
    loadAttributes(this.value);
});

// بار اول با دسته فعلی محصول
@if($product->category_id)
    loadAttributes({{ $product->category_id }});
@endif
</script>
@endsection

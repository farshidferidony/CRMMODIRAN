<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function store(Request $request, ProductCategory $category)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:190',
            'type'   => 'required|in:text,select,number',
            'values' => 'nullable|string',
        ]);

        ProductAttribute::create([
            'category_id' => $category->id,
            'name'        => $data['name'],
            'type'        => $data['type'],
            'values'      => $data['type'] === 'select' ? ($data['values'] ?? null) : null,
        ]);

        return back()->with('success','ویژگی اضافه شد.');
    }

    public function update(Request $request, ProductAttribute $attribute)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:190',
            'type'   => 'required|in:text,select,number',
            'values' => 'nullable|string',
        ]);

        $attribute->update([
            'name'   => $data['name'],
            'type'   => $data['type'],
            'values' => $data['type'] === 'select' ? ($data['values'] ?? null) : null,
        ]);

        return back()->with('success','ویژگی ویرایش شد.');
    }


    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();
        return back()->with('success','ویژگی حذف شد.');
    }
}


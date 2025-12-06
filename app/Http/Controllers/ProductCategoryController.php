<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::with('parent')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->paginate(20);

        $parents = ProductCategory::orderBy('name')->get();

        return view('product_categories.index', compact('categories','parents'));
    }

    public function create()
    {
        $parents = ProductCategory::orderBy('name')->get();
        return view('product_categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:190',
            'parent_id'  => 'nullable|exists:product_categories,id',
            'description'=> 'nullable|string',
        ]);

        ProductCategory::create($data);

        return redirect()->route('product-categories.index')
            ->with('success','دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function edit(ProductCategory $product_category)
    {
        $product_category->load('attributes');
        $parents = ProductCategory::where('id','!=',$product_category->id)
            ->orderBy('name')->get();

        return view('product_categories.edit', [
            'category' => $product_category,
            'parents'  => $parents,
        ]);
    }


    // public function edit(ProductCategory $product_category)
    // {
    //     $parents = ProductCategory::where('id','!=',$product_category->id)
    //         ->orderBy('name')->get();

    //     return view('product_categories.edit', [
    //         'category' => $product_category,
    //         'parents'  => $parents,
    //     ]);
    // }

    public function update(Request $request, ProductCategory $product_category)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:190',
            'parent_id'  => 'nullable|exists:product_categories,id|not_in:'.$product_category->id,
            'description'=> 'nullable|string',
        ]);

        $product_category->update($data);

        return redirect()->route('product-categories.index')
            ->with('success','دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(ProductCategory $product_category)
    {
        $product_category->delete();
        return redirect()->route('product-categories.index')
            ->with('success','دسته‌بندی حذف شد.');
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductAttribute;
use App\Models\Product;
use App\Models\ProductAttributeValue;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    // public function index()
    // {
    //     // print_r("dd");die();
    //     $products = Product::with('category')->orderBy('id','desc')->paginate(20);
    //     return view('products.index', compact('products'));
    // }

    public function index(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->get();

        $query = Product::with('category');

        // فیلتر دسته
        $selectedCategory = null;
        $filterAttributes = collect();

        if ($request->filled('category_id')) {
            $selectedCategory = ProductCategory::find($request->input('category_id'));
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
                // همین متدی که قبل ساختیم برای ارث‌بری:
                $filterAttributes = $selectedCategory->inheritedAttributes();
            }
        }

        // فیلتر نام و قیمت مثل قبل
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where('name','like',"%{$q}%");
        }
        if ($request->filled('price_min')) {
            $query->where('price','>=',$request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price','<=',$request->input('price_max'));
        }

        // فیلتر داینامیک بر اساس attributeها
        $attrFilters = $request->input('attr', []); // ساختار: ['attribute_id' => 'value']

        foreach ($attrFilters as $attrId => $attrValue) {
            if ($attrValue === null || $attrValue === '') continue;

            $query->whereHas('attributeValues', function($q) use ($attrId, $attrValue) {
                $q->where('attribute_id', $attrId)
                ->where('value', $attrValue);
            });
        }

        $products = $query->orderBy('id','desc')->paginate(20)
            ->appends($request->query());

        return view('products.index', compact(
            'products',
            'categories',
            'selectedCategory',
            'filterAttributes'
        ));
    }



    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        // مقادیر ویژگی‌ها را به صورت [attribute_id => value] برای پر کردن فرم آماده می‌کنیم
        $attributeValues = $product->attributeValues()
            ->pluck('value', 'attribute_id')
            ->toArray();

        return view('products.edit', compact('product','categories','attributeValues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }


    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:190',
            'category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'stock'       => 'nullable|integer',
            'attributes'  => 'array',         // [attribute_id => value]
            'attributes.*'=> 'nullable',      // خود مقادیر
        ]);

        $product = Product::create([
            'name'        => $data['name'],
            'category_id' => $data['category_id'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? 0,
            'stock'       => $data['stock'] ?? 0,
        ]);

        // ذخیره مقادیر ویژگی‌ها
        if(!empty($data['attributes'])){
            foreach($data['attributes'] as $attrId => $value){
                if($value === null || $value === '') continue;

                ProductAttributeValue::create([
                    'product_id'   => $product->id,
                    'attribute_id' => $attrId,
                    'value'        => $value,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success','محصول با موفقیت ایجاد شد.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:190',
            'category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'stock'       => 'nullable|integer',
            'attributes'  => 'array',
            'attributes.*'=> 'nullable',
        ]);

        $product->update([
            'name'        => $data['name'],
            'category_id' => $data['category_id'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? 0,
            'stock'       => $data['stock'] ?? 0,
        ]);

        // sync مقادیر ویژگی‌ها
        $product->attributeValues()->delete(); // ساده‌ترین راه؛ در صورت نیاز می‌توانی optimize کنی

        if(!empty($data['attributes'])){
            foreach($data['attributes'] as $attrId => $value){
                if($value === null || $value === '') continue;

                ProductAttributeValue::create([
                    'product_id'   => $product->id,
                    'attribute_id' => $attrId,
                    'value'        => $value,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success','محصول به‌روزرسانی شد.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('stock')->get();
        // dd($products);

        $product_code = null;

        if ($products !== null) {
            $product_code = 'PRD-' . count($products) + 1;
        } else {
            $product_code = 'PRD-1';
        }
        return Inertia::render('Admin/ProductPage', [
            'products' => $products,
            'product_code' => $product_code
        ]);
    }

    public function store(Request $request)
    {
        $product = new Product();
        $countOfProduct = count(Product::get());
        if ($countOfProduct == 0) {
            $productCode = 'PRD-1';
        } else {
            $productCode = 'PRD-' . ($countOfProduct + 1);
        }
        $product->product_name = $request->product_name;
        $product->product_code = $productCode;
        $product->minimum_qty = $request->minimum_qty;
        $product->save();

        return redirect()->route('admin.product.index')->with('success', 'Product has been added successfully.');
    }

    public function update(Request $request, $slug)
    {
        $product = Product::with('stock')->where('slug', $slug)->firstOrFail();

        $product->product_name = $request->product_name;
        $product->minimum_qty = $request->minimum_qty;

        if($product->stock){
            $inStock = $product->stock->qty >= $request->minimum_qty;
            $product->inStock = $inStock;
        }

        $product->update();

        return redirect()->route('admin.product.index')->with('success', 'Product has been updated successfully.');
    }

    public function destroy($slug)
    {
        $product = Product::with('stock')->where('slug', $slug)->firstOrFail();

        if ($product->stock !== null) {
            $product->stock->delete();
        }
        $product->delete();

        return redirect()->route('admin.product.index')->with('success', 'Product has been deleted successfully.');
    }
}

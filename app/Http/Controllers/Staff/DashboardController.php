<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(){
        $products = Product::with('stock')->get();

        return Inertia::render('Staff/DashboardPage', [
            'products' => $products,
        ]);
    }
}

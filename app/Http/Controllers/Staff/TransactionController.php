<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('stock')->get();
        $transactions = Transaction::with('type', 'user')->get();

        $types = TransactionType::get();

        return Inertia::render('Staff/TransactionPage', [
            'products' => $products,
            'transactions' => $transactions,
            'types' => $types,
        ]);
    }

    
}

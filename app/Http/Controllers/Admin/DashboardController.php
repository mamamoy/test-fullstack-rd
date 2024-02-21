<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('type', 'approvedBy','user', 'detail.product')->get();
        
        $send = $transactions->where('transaction_type_id', 2);
        $receive = $transactions->where('transaction_type_id', 1);

        $products = Product::with('stock')->get();

        $neededStock = [];

        foreach ($products as $product) {
            $percentage =  ($product->stock->qty / $product->minimum_qty) * 100;
            if ($percentage < 100) {
                $neededStock[] = [
                    'product_name' => $product->product_name,
                    'percentage' => round($percentage),
                ];
            }
        }
        return Inertia::render('Admin/DashboardPage', [
            'transactions' => $transactions,
            'products' => $products,
            'send' => $send,
            'receive' => $receive,
            'neededStocks' => $neededStock,
        ]);
    }

    public function update(Request $request, $transaction_id) //function to approve transaction
    {
        $user = Auth::user();

        if ($user->isAdmin = 1) {
            $transaction = Transaction::where('transaction_id', $transaction_id)->firstOrFail();
            $transaction->isApproved = $request->is_approved;
            $transaction->approved_by = $user->id;

            $details = TransactionDetail::where('transaction_id', $request->id)->get();
            if(count($details) > 1){
                foreach($details as $detail){
                    $stock = Stock::where('product_id', $detail->product_id)->firstOrFail();
                    if($transaction->transaction_type_id === 1){
                        $stock->qty += $detail->qty;
                        $stock->update();
                    } elseif ($transaction->transaction_type_id === 2) {
                        $stock->qty -= $detail->qty;
                        $stock->update();
                    }
                }
            }
            $transaction->update();

            return redirect()->route('admin.dashboard')->with('success', 'Transaction has been approved');
        } else {
            return redirect()->back()->with('error', 'You dont have access!');
        }
    }

    public function exportPdf($transaction_id)
    {
        $transaction = Transaction::with('detail.product', 'approvedBy', 'user', 'type')->where('transaction_id', $transaction_id)->first();

        $total = $transaction->detail->sum('qty');
        
        
        // dd($transaction);
        return view('TransactionDetails', [
            'transaction'=>$transaction,
            'total' => $total,
        ]);


        $pdf = FacadePdf::loadView('TransactionDetails', [
            'transaction' => $transaction,
            'total' => $total,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('transaction');
    }

    public function destroy($transaction_id)
    {
        $user = Auth::user();
        if ($user->isAdmin === 1) {
            $transaction = Transaction::with('detail')->where('transaction_id', $transaction_id)->firstOrFail();

            foreach ($transaction->detail as $detail) {
                $detail->delete();
            }

            $transaction->delete();

            return redirect()->route('admin.dashboard')->with('success', 'Transaction has been deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'You dont have access');
        }
    }
}

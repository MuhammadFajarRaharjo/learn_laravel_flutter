<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\TransactionDetails;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Get Transaction
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        // mengambil Transaction product from id
        if ($id) {
            try {
                $transactions = Transaction::with('details.product')->findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return ResponseFormatter::error(
                    message: 'Data transaksi tidak ada',
                    code: $e->getCode()
                );
            }
            return ResponseFormatter::success($transactions, 'Data transaksi berhasil di ambil');
        }

        $transactions = Transaction::with('details.product')->when('users_id', Auth::user()->id);

        if ($status) {
            $transactions->when('status', $status);
        }

        return ResponseFormatter::success($transactions->paginate($limit), 'Data transaksi berhasil di ambil');
    }

    // Checkout Transaction
    public function checkout(Request $request)
    {
        try {
            // validation request
            $request->validate([
                'details' => ['required', 'array'],
                'details.*.' => ['exists:products,id'],
                'total_price' => 'required',
                'shipping_price' => 'required',
                'status' => [
                    'required',
                    'in:PENDING, SUCCESS, CANCELED, FAILED, SHIPPING, SHIPPED'
                ]
            ]);

            // create transaction
            $transactions = Transaction::create([
                'users_id' => Auth::user()->id,
                'address' => $request->address,
                'shipping_price' => $request->shipping_price,
                'total_price' => $request->total_price,
                'status' => $request->status
            ]);

            // create details transaction
            foreach ($request->details as $product) {
                TransactionDetails::create([
                    'users_id' => Auth::user()->id,
                    'products_id' => $product['product_id'],
                    'transactions_id' => $transactions->id,
                    'quantity' => $product['quantity']
                ]);
            }

            // load data detail transaction product karena tidak langsung ke update. butuh di load
            return ResponseFormatter::success($transactions->load('details.product'), 'Transaction success');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something when Wrong',
                'error' => $e->getMessage()
            ], 'Transaction error');
        }
    }
}

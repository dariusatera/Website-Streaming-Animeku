<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction; // <--- WAJIB DI-IMPORT

class PaymentController extends Controller
{
    public function __construct()
    {
        // Sesuaikan dengan data .env Anda
        Config::$serverKey = env('Rahasia-biar bisa dipush ke github'); // Pastikan Key Benar
        Config::$isProduction = false; 
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Silakan login terlebih dahulu!'], 401);
        }

        $user = Auth::user();
        $paket = $request->paket; // 'bulanan' atau 'tahunan'
        $harga = $request->harga; 

        // Buat Order ID Unik
        $orderId = 'ORDER-' . $user->id . '-' . time() . '-' . rand(100, 999);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $harga,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $paket,
                    'price' => $harga,
                    'quantity' => 1,
                    'name' => 'Premium ' . ucfirst($paket)
                ]
            ],
            // Redirect Finish (Pastikan route ini ada di web.php)
            'callbacks' => [
                'finish' => url('/payment/finish') 
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // FUNGSI INI SEKARANG AMAN ✅
    public function finishPayment(Request $request)
    {
        $orderId = $request->get('order_id');

        // Jika user tidak login atau tidak ada Order ID, tendang ke home
        if (!Auth::check() || !$orderId) {
            return redirect('/login');
        }

        $user = Auth::user();

        try {
            // Cek Status Transaksi ke Midtrans
            $status = Transaction::status($orderId);
            $transactionStatus = $status->transaction_status;

            // Jika status Settlement (Lunas) atau Capture (Kartu Kredit Sukses)
            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                
                // Baru aktifkan VIP
                $user->subscription_type = 'VIP'; 
                $user->save();

                return redirect('/')->with('success', 'Pembayaran Berhasil! Anda sekarang VIP.');
            
            } else if ($transactionStatus == 'pending') {
                return redirect('/')->with('info', 'Pembayaran tertunda. Silakan selesaikan pembayaran.');
            } else {
                return redirect('/')->with('error', 'Pembayaran gagal atau kedaluwarsa.');
            }

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Transaksi tidak ditemukan.');
        }
    }
}
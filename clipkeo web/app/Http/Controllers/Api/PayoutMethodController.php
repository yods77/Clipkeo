<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutMethodController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil user_id dari parameter URL (?user_id=...)
        $userId = $request->user_id;

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID wajib dikirim!'
            ], 400);
        }

        // 2. Ambil data dari tabel 'payout_methods'
        // Sesuaikan nama tabel dengan database Bosku (lihat gambar yang dikirim)
        $data = DB::table('payout_methods')
                    ->where('user_id', $userId)
                    ->get();

        // 3. Kirim balik sebagai JSON
        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }
}
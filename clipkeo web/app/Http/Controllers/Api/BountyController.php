<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bounty;
use Illuminate\Http\Request;

class BountyController extends Controller
{
    public function index(Request $request)
    {
        $query = Bounty::where('status', 'active')
            ->orderBy('created_at', 'desc');

        // 1. Filter Pencarian (Judul atau Brand/Publisher)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 2. Filter Kategori
        if ($request->has('category') && $request->filled('category')) {
            $category = $request->category;
            if ($category !== 'Semua' && $category !== 'All') {
                $query->where('category', $category);
            }
        }

        // Ambil data dengan Pagination (10 per halaman)
        $bounties = $query->paginate(10);

        // Format Data Agar Mudah Dipakai Flutter
        $data = $bounties->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->category,
                'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                'reward_amount' => (int) $item->reward_amount, // Cast ke integer
                'remaining_budget' => (int) $item->remaining_budget,
                'platform' => $item->platform, // Array
                'distribution_strategy' => $item->distribution_strategy,
                'min_views' => $item->min_views,
                'company_name' => $item->publisher->name ?? 'Unknown Brand', // Ambil nama publisher
                'deadline_at' => $item->deadline_at ? \Carbon\Carbon::parse($item->deadline_at)->format('d M Y') : '-',
                'required_mentions' => $item->required_mentions,
                'required_hashtags' => $item->required_hashtags,
                'rules' => $item->rules,
                'rate_per_view' => $item->rate_per_view,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'current_page' => $bounties->currentPage(),
                'last_page' => $bounties->lastPage(),
                'total' => $bounties->total(),
            ]
        ]);
    }
    public function getCategories()
    {
        // Ambil kolom category dari bounty aktif, unique, urut abjad
        $categories = Bounty::where('status', 'active')
            ->select('category')
            ->distinct()
            ->orderBy('category', 'asc')
            ->pluck('category')
            ->toArray();

        // Tambahkan 'Semua' di paling awal
        array_unshift($categories, 'Semua');

        return response()->json($categories);
    }

    /*
    |--------------------------------------------------------------------------
    | API: Detail Campaign (Wajib untuk Halaman Detail)
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        // Cari data berdasarkan ID
        $bounty = Bounty::with('publisher')->find($id);

        // Jika tidak ketemu, kirim error 404
        if (!$bounty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign not found'
            ], 404);
        }

        // Format Data Detail Lengkap
        $data = [
            'id' => $bounty->id,
            'title' => $bounty->title,
            'category' => $bounty->category,
            'description' => $bounty->description, // Penting untuk tab Deskripsi
            
            // Gambar: Pastikan logic-nya sama dengan index
            'image_url' => $bounty->image_path ? asset('storage/' . $bounty->image_path) : null,
            'image_path' => $bounty->image_path, // Cadangan untuk Flutter
            
            'company_name' => $bounty->publisher->name ?? 'Unknown Brand',
            'reward_amount' => (int) $bounty->reward_amount,
            'remaining_budget' => (int) $bounty->remaining_budget,
            'min_views' => $bounty->min_views,
            'platform' => $bounty->platform, // Array ['tiktok', 'instagram']
            
            // Data Khusus Detail
            'source_link' => $bounty->source_link, // Untuk tombol Download
            'required_hashtags' => $bounty->required_hashtags, // Untuk caption otomatis
            'required_mentions' => $bounty->required_mentions, // Untuk caption otomatis
            'distribution_strategy' => $bounty->distribution_strategy, // 'performance_tiered' atau 'cpm'
            'rate_per_view' => $bounty->rate_per_view, // Harga per view untuk CPM
            'rules' => $bounty->rules, // JSON Array untuk Tiered (Target & Reward)
            
            // Format Tanggal
            'deadline_at' => $bounty->deadline_at ? \Carbon\Carbon::parse($bounty->deadline_at)->format('d M Y') : '-',
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // ... method index dan show yang sudah ada ...

    // 👇 TAMBAHKAN FUNGSI INI DI PALING BAWAH CLASS
    public function myActiveMissions(Request $request)
    {
        // Ambil User yang sedang login
        $user = $request->user();

        // Cek apakah User punya relasi ke Submission/History
        // ASUMSI: Kamu punya tabel 'submissions' atau relasi 'submissions' di User Model
        // Jika nama tabelmu beda, ganti 'submissions' dengan nama yang benar (misal: 'histories')
        
        // Kalau tabelnya belum ada, kita return array kosong dulu biar Flutter gak Error 404
        if (!method_exists($user, 'submissions')) {
             return response()->json([
                'status' => 'success',
                'data' => [] // Return kosong dulu
            ]);
        }

        $activities = $user->submissions()
            ->with('bounty') // Load data Bounty/Campaign-nya juga
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Format datanya biar Flutter enak bacanya
        $data = $activities->map(function($item) {
            $amount = $item->prize_amount ?? $item->earned_amount ?? $item->bounty->reward_amount ?? 0;
            return [
                'id' => $item->id,
                'status' => $item->status, // pending, approved, rejected
                'created_at' => $item->created_at->diffForHumans(), // Contoh: "2 hours ago"
                'prize_amount' => (int) $amount,
                'views_count' => (int) $item->views_count,
                
                // Masukkan data Campaign/Bounty di sini
                'campaign' => [
                    'title' => $item->bounty->title ?? 'Misi Dihapus',
                    'image_url' => $item->bounty->image_path ? asset('storage/' . $item->bounty->image_path) : null,
                    'distribution_strategy' => $item->bounty->distribution_strategy ?? 'cpm',
                    'rate_per_view' => $item->bounty->rate_per_view ?? 0,
                    'rules' => $item->bounty->rules ?? null,
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
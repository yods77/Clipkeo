<?php

namespace App\Helpers;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;

class FcmHelper
{
    /**
     * Kirim Notifikasi ke User (Versi High Priority V1)
     * * @param  mixed  $user    Object User (harus punya kolom fcm_token)
     * @param  string $title   Judul Notifikasi
     * @param  string $body    Isi Pesan
     * @param  array  $data    Data tambahan (opsional)
     * @return mixed
     */
    public static function sendToUser($user, $title, $body, $data = [])
    {
        // 1. Cek User & Token
        if (!$user || empty($user->fcm_token)) {
            Log::warning("FCM: Gagal kirim. User/Token kosong.");
            return false;
        }

        // 2. Setup File JSON
        $credentialsPath = storage_path('app/firebase_credentials.json');
        if (!file_exists($credentialsPath)) {
            Log::error("FCM: File JSON credential hilang di storage/app/.");
            return false;
        }

        try {
            // 3. Baca Project ID
            $jsonContent = file_get_contents($credentialsPath);
            $jsonData = json_decode($jsonContent, true);
            $projectId = $jsonData['project_id'] ?? null;

            // 4. Setup Google Client
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            // 5. Setup URL
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // --- [UPDATE PENTING MULAI DI SINI] ---
            
            // Konversi semua data tambahan menjadi string (Wajib buat Android)
            // Mengatasi error jika ada data bertipe integer/null
            $stringData = array_map(function($value) {
                return (string) $value;
            }, $data);

            $payload = [
                'message' => [
                    'token' => $user->fcm_token,
                    
                    // Notifikasi Visual (Title & Body)
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],

                    // Data Tambahan (Logic di App)
                    'data' => $stringData,

                    // 🔥 KONFIGURASI KHUSUS ANDROID (AGAR BUNYI SAAT LAYAR MATI)
                    'android' => [
                        'priority' => 'high', // Memaksa bangun dari Doze mode
                        'notification' => [
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                            'visibility' => 'public', // Muncul di lockscreen
                        ]
                    ]
                ]
            ];
            // --- [UPDATE SELESAI] ---

            // 6. Kirim Pakai CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
            $result = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            
            curl_close($ch);

            // Log Hasil Singkat
            Log::info("FCM Sent to {$user->name}. Result Length: " . strlen($result));
            
            return json_decode($result);

        } catch (\Exception $e) {
            Log::error("FCM Helper Error: " . $e->getMessage());
            return false;
        }
    }
}
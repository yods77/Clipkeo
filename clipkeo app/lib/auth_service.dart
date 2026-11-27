import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'config.dart';

class AuthService {
  // --- KONFIGURASI ---
  // Pastikan IP ini benar (Cek pakai ipconfig di cmd laptop)
  // Jika pakai Emulator Android Studio, ganti jadi "http://10.0.2.2:8000/api"
  final String baseUrl = AppConfig.baseUrl;

  /**
   * -- Fungsi Login (REVISI: STRICT MODE) --
   * Sekarang kita hanya fokus mengambil data dari key 'user'.
   * Tidak ada lagi tebak-tebakan 'data' atau 'root'.
   */
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}/login'),
        headers: {'Accept': 'application/json'},
        body: {'email': email, 'password': password},
      );


      if (response.statusCode == 200) {
        var data = jsonDecode(response.body);
        await _performSaveSession(data);

        // 1. SIMPAN TOKEN
        SharedPreferences prefs = await SharedPreferences.getInstance();
        String token = data['access_token'] ?? data['token'] ?? '';
        await prefs.setString('token', token);

        // 2. AMBIL DATA USER (LOGIKA TEGAS)
        // Kita cari langsung di key 'user' sesuai janji Laravel
        Map<String, dynamic>? userData;

        if (data['user'] != null) {
          userData = Map<String, dynamic>.from(data['user']);
        }
        // Fallback: Jaga-jaga kalau Laravel lupa di-update, kita cek 'data'
        else if (data['data'] != null) {
          userData = Map<String, dynamic>.from(data['data']);
        }

        // 3. LOGIKA PENENTUAN "USER BARU"
        bool isNewUser = false;

        if (userData != null) {
          // Kita simpan Nama & Email ke HP biar loading profil cepet
          await prefs.setString('user_name', userData['name'] ?? '');
          await prefs.setString('user_email', userData['email'] ?? '');

          // Cek apakah namanya kosong?
          String nameCheck = userData['name'] ?? '';
          if (nameCheck.trim().isEmpty) {
            isNewUser = true; // Nama kosong -> Lempar ke Isi Nama
          }
        } else {
          // BAHAYA: Login sukses tapi data user gak kebaca
          // Jangan anggap user baru, tapi anggap Error biar kita sadar
          return {
            'success': false,
            'message': 'Gagal membaca data user dari server.',
          };
        }

        return {
          'success': true,
          'is_new_user': isNewUser,
          'message': 'Login Berhasil',
          'user': userData,
        };
      } else {
        // Handle Error Login (Password salah dll)
        final errorData = jsonDecode(response.body);
        return {
          'success': false,
          'is_new_user': false,
          'message': errorData['message'] ?? 'Email atau Password salah',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'is_new_user': false,
        'message': 'Error koneksi: $e',
      };
    }
  }

  /**
   * -- Fungsi Register --
   * Auto Login setelah daftar sukses.
   */
  Future<bool> register(
    String name,
    String email,
    String password,
    String phoneNumber,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/register'),
        body: {
          'name': name,
          'email': email,
          'password': password,
          'phone_number': phoneNumber,
        },
      );


      if (response.statusCode == 201 || response.statusCode == 200) {

        // Panggil login supaya Token tersimpan di SharedPreferences
        await login(email, password);

        return true;
      } else {
        return false;
      }
    } catch (e) {
      return false;
    }
  }

  Future<void> _performSaveSession(Map<String, dynamic> result) async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();

      String? userIdToSave;
      String? userNameToSave;
      String? tokenToSave;

      // 1. Ambil Data User (Cek di root atau di dalam 'data')
      var userData = result['user'] ?? result['data']?['user'];
      if (userData != null) {
        userIdToSave = userData['id'].toString();
        userNameToSave = userData['name'].toString();
      }

      // 2. Ambil Token (PENTING: Laravel kamu pakai 'access_token')
      tokenToSave =
          result['access_token'] ?? result['token'] ?? result['data']?['token'];

      // 3. Eksekusi Simpan ke Memori HP
      if (userIdToSave != null) {
        await prefs.setString('user_id', userIdToSave);
        if (userNameToSave != null) {
          await prefs.setString('user_name', userNameToSave);
        }

        if (tokenToSave != null && tokenToSave.isNotEmpty) {
          await prefs.setString('token', tokenToSave);
        }
      }
    } catch (_) {
      // Authentication data must never be written to logs.
    }
  }

  // Revoke the current API token before clearing the local session.
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token != null && token.isNotEmpty) {
      try {
        await http
            .post(
              Uri.parse('$baseUrl/logout'),
              headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer $token',
              },
            )
            .timeout(const Duration(seconds: 5));
      } catch (_) {
        // Always clear the local session, even if the device is offline.
      }
    }

    // Keep unresolved withdrawal idempotency records across logout. The
    // records contain only a request key and its amount/bank fingerprint, not
    // the bearer token or transaction PIN. The next login for the same user
    // can safely reconcile the original request.
    final pendingWithdrawalAttempts = <String, String>{};
    for (final key in prefs.getKeys()) {
      if (key.startsWith('wallet_pending_withdrawal_attempt_')) {
        final value = prefs.getString(key);
        if (value != null) pendingWithdrawalAttempts[key] = value;
      }
    }

    await prefs.clear();

    for (final entry in pendingWithdrawalAttempts.entries) {
      await prefs.setString(entry.key, entry.value);
    }
  }

  Future<Map<String, dynamic>> loginWithGoogle({
    required String idToken,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login-google'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'id_token': idToken}),
      );

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200) {
        await _performSaveSession(data);
        return {'success': true, 'data': data};
      }

      return {
        'success': false,
        'message': data['message'] ?? 'Google Sign-In gagal diverifikasi.',
      };
    } catch (_) {
      return {
        'success': false,
        'message': 'Tidak dapat terhubung ke server. Silakan coba lagi.',
      };
    }
  }

  // auth_service.dart

Future<Map<String, dynamic>> forgotPassword(String email) async {
  try {
    final response = await http.post(
      Uri.parse('$baseUrl/forgot-password'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'email': email}),
    );

    return jsonDecode(response.body);
  } catch (_) {
    return {'success': false, 'message': 'Kesalahan koneksi'};
  }
}

  Future<Map<String, dynamic>> resetPassword({
    required String email,
    required String otp,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/reset-password'),
      body: {
        'email': email,
        'otp': otp,
        'password': password,
        'password_confirmation': password,
      },
    );
    return jsonDecode(response.body);
  }
}

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config.dart';

/*
|--------------------------------------------------------------------------
| WALLET SERVICE (UPDATED)
|--------------------------------------------------------------------------
| Mengelola Saldo, Request Withdraw, dan Data Bank User.
*/
class WalletService {
  // ⚠️ Pastikan IP ini sesuai dengan IP Laptop/Server Laravel Bosku
  final String baseUrl = AppConfig.baseUrl;

Future<Map<String, String>> _getHeaders() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');
    final headers = <String, String>{'Accept': 'application/json'};
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // --- 1. CEK STATUS PIN ---
  Future<bool> checkPinStatus() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(
        Uri.parse('$baseUrl/wallet/check-pin'),
        headers: headers,
      );


      if (response.statusCode == 200) {
        var data = jsonDecode(response.body);
        bool status = data['has_pin'] ?? false;
        return status;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  // --- 2. SETUP PIN BARU ---
  Future<bool> setupPin(String newPin) async {
    try {
      final headers = await _getHeaders();
      final response = await http.post(
        Uri.parse('$baseUrl/wallet/setup-pin'),
        headers: headers,
        body: {
          'pin': newPin,
          'pin_confirmation': newPin,
        },
      );


      if (response.statusCode == 200) {
        return true;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  /**
   * -- GET DASHBOARD --
   * Mengambil saldo dan riwayat transaksi.
   */
  Future<Map<String, dynamic>> getWalletDashboard() async {
    try {
      final headers = await _getHeaders();
      if (!headers.containsKey('Authorization')) return {};

      final response = await http.get(
        Uri.parse('$baseUrl/wallet/dashboard'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body)['data'];
      }
      return {};
    } catch (_) {
      return {};
    }
  }

  /**
   * -- GET PAYOUT METHODS (FUNGSI BARU) --
   * Mengambil daftar bank yang tersimpan di database user.
   * Dipakai di halaman WithdrawInputScreen.
   */
  Future<List<dynamic>> getPayoutMethods() async {
    try {
      final headers = await _getHeaders();
      if (!headers.containsKey('Authorization')) return [];

      final response = await http.get(
        Uri.parse('$baseUrl/payout-methods'),
        headers: headers,
      );
      

      if (response.statusCode == 200) {
        var data = jsonDecode(response.body);
        return data['data'] ?? []; 
      } else {
        return [];
      }
    } catch (_) {
      return [];
    }
  }

  /**
   * -- REQUEST WITHDRAW --
   * Mengirim permintaan penarikan dana.
   */
  Future<bool> requestWithdraw({required String amount, required int methodId}) async {
    try {
      final headers = await _getHeaders();
      if (!headers.containsKey('Authorization')) return false;


      final response = await http.post(
        Uri.parse('$baseUrl/wallet/withdraw'),
        body: {
          'amount': amount,
          'payout_method_id': methodId.toString(),
        },
        headers: headers,
      );

      
      if (response.statusCode == 200) {
        return true;
      } else {
        return false;
      }
    } catch (_) {
      return false;
    }
  }
}
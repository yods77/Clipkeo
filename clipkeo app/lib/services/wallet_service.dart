import 'dart:convert';
import 'dart:math';

import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../config.dart';

enum WithdrawalRequestStatus {
  success,
  rejected,
  uncertain,
  pendingRequestMismatch,
}

class WithdrawalRequestResult {
  final WithdrawalRequestStatus status;
  final String message;
  final int? withdrawalId;

  const WithdrawalRequestResult({
    required this.status,
    required this.message,
    this.withdrawalId,
  });

  bool get isSuccess => status == WithdrawalRequestStatus.success;
}

class WalletService {
  static const String _pendingWithdrawalStorageKeyPrefix =
      'wallet_pending_withdrawal_attempt_';

  final String baseUrl = AppConfig.baseUrl;
  final http.Client _client;

  WalletService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final headers = <String, String>{'Accept': 'application/json'};

    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }

  String _createIdempotencyKey() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));

    // RFC 4122 UUID v4 variant and version bits.
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;

    final hex = bytes
        .map((byte) => byte.toRadixString(16).padLeft(2, '0'))
        .join();

    return '${hex.substring(0, 8)}-${hex.substring(8, 12)}-'
        '${hex.substring(12, 16)}-${hex.substring(16, 20)}-'
        '${hex.substring(20, 32)}';
  }

  String _pendingStorageKeyFor(Map<String, String> headers) {
    final authorization = headers['Authorization'];
    if (authorization == null || authorization.isEmpty) {
      throw StateError('Authenticated user is required for pending withdrawal state.');
    }

    // Scope local retry state to the authenticated account without storing
    // the bearer token a second time in the preference key.
    final fingerprint = sha256.convert(utf8.encode(authorization)).toString();
    return '$_pendingWithdrawalStorageKeyPrefix$fingerprint';
  }

  Future<Map<String, dynamic>?> _readPendingWithdrawalAttempt(
    String storageKey,
  ) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(storageKey);

    if (raw == null) return null;

    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) return decoded;
    } catch (_) {
      // Keep malformed state rather than silently creating a new key. A new
      // withdrawal must not be sent while an earlier request is unresolved.
    }

    return <String, dynamic>{'invalid': true};
  }

  Future<Map<String, dynamic>?> getPendingWithdrawalAttempt() async {
    final headers = await _getHeaders();
    if (!headers.containsKey('Authorization')) return null;

    return _readPendingWithdrawalAttempt(_pendingStorageKeyFor(headers));
  }

  Future<void> _clearPendingWithdrawalAttempt(String storageKey) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(storageKey);
  }

  // --- 1. CEK STATUS PIN ---
  Future<bool> checkPinStatus() async {
    try {
      final headers = await _getHeaders();
      final response = await _client.get(
        Uri.parse('$baseUrl/wallet/check-pin'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['has_pin'] ?? false;
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
      final response = await _client.post(
        Uri.parse('$baseUrl/wallet/setup-pin'),
        headers: headers,
        body: {
          'pin': newPin,
          'pin_confirmation': newPin,
        },
      );

      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // --- 3. GET DASHBOARD ---
  Future<Map<String, dynamic>> getWalletDashboard() async {
    try {
      final headers = await _getHeaders();
      if (!headers.containsKey('Authorization')) return {};

      final response = await _client.get(
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

  // --- 4. GET PAYOUT METHODS ---
  Future<List<dynamic>> getPayoutMethods() async {
    try {
      final headers = await _getHeaders();
      if (!headers.containsKey('Authorization')) return [];

      final response = await _client.get(
        Uri.parse('$baseUrl/payout-methods'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['data'] ?? [];
      }

      return [];
    } catch (_) {
      return [];
    }
  }

  // --- 5. REQUEST WITHDRAW ---
  // The idempotency key and original payload are persisted before sending.
  // A retry after timeout/app restart must reuse both values.
  Future<WithdrawalRequestResult> requestWithdraw({
    required String amount,
    required int methodId,
    required String pin,
  }) async {
    final headers = await _getHeaders();
    if (!headers.containsKey('Authorization')) {
      return const WithdrawalRequestResult(
        status: WithdrawalRequestStatus.rejected,
        message: 'Sesi login tidak ditemukan. Silakan login kembali.',
      );
    }

    final prefs = await SharedPreferences.getInstance();
    final pendingStorageKey = _pendingStorageKeyFor(headers);
    Map<String, dynamic>? pending;

    try {
      pending = await _readPendingWithdrawalAttempt(pendingStorageKey);
    } catch (_) {
      return const WithdrawalRequestResult(
        status: WithdrawalRequestStatus.uncertain,
        message:
            'Status permintaan sebelumnya belum dapat dibaca. Jangan membuat penarikan baru sebelum statusnya diperiksa.',
      );
    }

    late final String idempotencyKey;

    if (pending != null) {
      if (pending['invalid'] == true) {
        return const WithdrawalRequestResult(
          status: WithdrawalRequestStatus.pendingRequestMismatch,
          message:
              'Data percobaan penarikan sebelumnya tidak dapat dibaca. Demi keamanan saldo, pengajuan baru ditahan dan perlu pemeriksaan.',
        );
      }

      final pendingAmount = pending['amount']?.toString();
      final pendingMethodId =
          int.tryParse(pending['payout_method_id']?.toString() ?? '');

      if (pendingAmount != amount || pendingMethodId != methodId) {
        return WithdrawalRequestResult(
          status: WithdrawalRequestStatus.pendingRequestMismatch,
          message:
              'Masih ada penarikan yang belum terkonfirmasi sebesar Rp $pendingAmount. Lanjutkan dengan nominal dan rekening yang sama agar tidak terjadi penarikan ganda.',
        );
      }

      idempotencyKey = pending['idempotency_key']?.toString() ?? '';
      if (idempotencyKey.isEmpty) {
        return const WithdrawalRequestResult(
          status: WithdrawalRequestStatus.pendingRequestMismatch,
          message:
              'Kunci permintaan sebelumnya tidak tersedia. Pengajuan baru ditahan demi keamanan saldo.',
        );
      }
    } else {
      idempotencyKey = _createIdempotencyKey();
      final saved = await prefs.setString(
        pendingStorageKey,
        jsonEncode({
          'idempotency_key': idempotencyKey,
          'amount': amount,
          'payout_method_id': methodId,
        }),
      );

      if (!saved) {
        return const WithdrawalRequestResult(
          status: WithdrawalRequestStatus.rejected,
          message:
              'Permintaan belum dikirim karena data pengaman tidak dapat disimpan di perangkat.',
        );
      }
    }

    headers['Idempotency-Key'] = idempotencyKey;

    try {
      final response = await _client
          .post(
            Uri.parse('$baseUrl/wallet/withdraw'),
            body: {
              'amount': amount,
              'payout_method_id': methodId.toString(),
              'pin': pin,
            },
            headers: headers,
          )
          .timeout(const Duration(seconds: 30));

      Map<String, dynamic> responseData = {};
      try {
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) responseData = decoded;
      } catch (_) {
        // A successful HTTP status is still authoritative even if a proxy
        // returned an unreadable body; the next wallet refresh can reconcile.
      }

      if (response.statusCode == 200 &&
          responseData['status'] == 'success') {
        await _clearPendingWithdrawalAttempt(pendingStorageKey);

        return WithdrawalRequestResult(
          status: WithdrawalRequestStatus.success,
          message: responseData['message']?.toString() ??
              'Permintaan penarikan diterima.',
          withdrawalId: int.tryParse(
            responseData['data']?['wd_id']?.toString() ?? '',
          ),
        );
      }

      final message = responseData['message']?.toString() ??
          'Server belum dapat memproses permintaan penarikan.';

      // These responses are produced before a withdrawal is committed.
      // A new user attempt may therefore use a new key.
      if (response.statusCode >= 400 &&
          response.statusCode < 500 &&
          response.statusCode != 409 &&
          response.statusCode != 429) {
        await _clearPendingWithdrawalAttempt();

        return WithdrawalRequestResult(
          status: WithdrawalRequestStatus.rejected,
          message: message,
        );
      }

      // 409 means the key/payload needs reconciliation. 429 and 5xx are
      // retryable/ambiguous, so preserve the same key and payload.
      return WithdrawalRequestResult(
        status: response.statusCode == 409
            ? WithdrawalRequestStatus.pendingRequestMismatch
            : WithdrawalRequestStatus.uncertain,
        message: response.statusCode == 409
            ? message
            : 'Status penarikan belum dapat dipastikan. Coba lagi dengan nominal dan rekening yang sama. $message',
      );
    } catch (_) {
      // Timeout or connection loss can happen after the server committed.
      // Keep the key so the next attempt replays instead of debiting twice.
      return const WithdrawalRequestResult(
        status: WithdrawalRequestStatus.uncertain,
        message:
            'Koneksi terputus dan status penarikan belum dapat dipastikan. Coba lagi dengan nominal serta rekening yang sama; jangan membuat pengajuan baru.',
      );
    }
  }
}

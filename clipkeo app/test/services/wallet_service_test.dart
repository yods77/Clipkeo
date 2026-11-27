import 'dart:convert';
import 'dart:io';

import 'package:clipkeo/services/wallet_service.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({
      'token': 'test-api-token',
      'user_id': 'test-user-1',
    });
  });

  group('WalletService withdrawal idempotency', () {
    test('retry after timeout reuses the same key and clears it after success', () async {
      final sentKeys = <String>[];
      var attempts = 0;

      final client = MockClient((request) async {
        attempts++;
        final keyEntry = request.headers.entries.firstWhere(
          (entry) => entry.key.toLowerCase() == 'idempotency-key',
        );
        sentKeys.add(keyEntry.value);

        if (attempts == 1) {
          throw const SocketException('simulated connection timeout');
        }

        return http.Response(
          jsonEncode({
            'status': 'success',
            'message': 'Penarikan diterima.',
            'data': {
              'wd_id': 42,
              'idempotent_replay': true,
            },
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      });
      addTearDown(client.close);

      final firstService = WalletService(client: client);
      final firstResult = await firstService.requestWithdraw(
        amount: '50000',
        methodId: 7,
        pin: '123456',
      );

      expect(firstResult.status, WithdrawalRequestStatus.uncertain);
      expect(await firstService.getPendingWithdrawalAttempt(), isNotNull);

      // A new service instance simulates reopening the withdrawal screen/app.
      final retryService = WalletService(client: client);
      final retryResult = await retryService.requestWithdraw(
        amount: '50000',
        methodId: 7,
        pin: '123456',
      );

      expect(retryResult.status, WithdrawalRequestStatus.success);
      expect(retryResult.withdrawalId, 42);
      expect(attempts, 2);
      expect(sentKeys, hasLength(2));
      expect(sentKeys[0], sentKeys[1]);
      expect(await retryService.getPendingWithdrawalAttempt(), isNull);
    });

    test('different details are blocked while an earlier request is uncertain', () async {
      var attempts = 0;

      final client = MockClient((request) async {
        attempts++;
        throw const SocketException('simulated connection timeout');
      });
      addTearDown(client.close);

      final service = WalletService(client: client);
      final firstResult = await service.requestWithdraw(
        amount: '50000',
        methodId: 7,
        pin: '123456',
      );

      expect(firstResult.status, WithdrawalRequestStatus.uncertain);

      final changedPayloadResult = await service.requestWithdraw(
        amount: '60000',
        methodId: 7,
        pin: '123456',
      );

      expect(
        changedPayloadResult.status,
        WithdrawalRequestStatus.pendingRequestMismatch,
      );
      expect(attempts, 1);
    });

    test('definitive validation rejection clears the pending attempt', () async {
      final client = MockClient((request) async {
        return http.Response(
          jsonEncode({
            'status': 'error',
            'message': 'PIN transaksi tidak valid.',
          }),
          401,
          headers: {'content-type': 'application/json'},
        );
      });
      addTearDown(client.close);

      final service = WalletService(client: client);
      final result = await service.requestWithdraw(
        amount: '50000',
        methodId: 7,
        pin: '000000',
      );

      expect(result.status, WithdrawalRequestStatus.rejected);
      expect(await service.getPendingWithdrawalAttempt(), isNull);
    });
  });
}

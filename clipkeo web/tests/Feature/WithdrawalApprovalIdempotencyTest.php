<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WithdrawalApprovalIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.env' => 'testing',
            'services.xendit.secret_key' => 'xnd_development_test_key',
        ]);
        Notification::fake();
        Mail::fake();
    }

    public function test_admin_approval_uses_one_stable_idempotency_key_and_does_not_resubmit_after_acceptance(): void
    {
        [$admin, $withdrawal, $transaction, $fee] = $this->createWithdrawal();

        $lookupAttempts = 0;
        Http::fake(function ($request) use ($withdrawal, &$lookupAttempts) {
            if ($request->method() === 'GET') {
                $lookupAttempts++;

                if ($lookupAttempts === 1) {
                    return Http::response([], 200);
                }

                return Http::response([[
                    'id' => 'xendit-disbursement-123',
                    'external_id' => 'WD-testing-' . $withdrawal->id,
                    'status' => 'PENDING',
                ]], 200);
            }

            return Http::response([
                'id' => 'xendit-disbursement-123',
                'external_id' => 'WD-testing-' . $withdrawal->id,
                'status' => 'PENDING',
            ], 200);
        });

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame('processing', $withdrawal->fresh()->status);
        $this->assertSame('xendit-disbursement-123', $transaction->fresh()->external_ref);
        $this->assertSame('pending', $fee->fresh()->status);

        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($withdrawal) {
            return $request->method() === 'POST'
                && $request->hasHeader('X-IDEMPOTENCY-KEY', 'clipkeo-testing-wd-' . $withdrawal->id)
                && $request['external_id'] === 'WD-testing-' . $withdrawal->id;
        });

        // A second admin click must not create another transfer after Xendit
        // has returned a provider disbursement ID.
        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        Http::assertSentCount(3);
    }

    public function test_admin_reconciles_an_ambiguous_timeout_before_any_second_transfer(): void
    {
        [$admin, $withdrawal, $transaction] = $this->createWithdrawal();
        $postAttempts = 0;
        $lookupAttempts = 0;
        $lookupExternalIds = [];
        $externalId = 'WD-testing-' . $withdrawal->id;

        Http::fake(function ($request) use (&$postAttempts, &$lookupAttempts, &$lookupExternalIds, $externalId) {
            if ($request->method() === 'GET') {
                $lookupAttempts++;
                $lookupExternalId = (string) $request['external_id'];
                $lookupExternalIds[] = $lookupExternalId;

                // The first lookup misses. On retry, the new reference is found;
                // the legacy reference is still checked and must not match it.
                if ($lookupExternalId !== $externalId || $lookupAttempts === 1) {
                    return Http::response([], 200);
                }

                return Http::response([[
                    'id' => 'xendit-disbursement-after-timeout',
                    'external_id' => $externalId,
                    'status' => 'PENDING',
                ]], 200);
            }

            $postAttempts++;
            throw new ConnectionException('Simulated network timeout');
        });

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame('processing', $withdrawal->fresh()->status);
        $this->assertNull($transaction->fresh()->external_ref);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame(
            'xendit-disbursement-after-timeout',
            $transaction->fresh()->external_ref
        );

        Http::assertSentCount(3);
        $this->assertSame(1, $postAttempts);
        $this->assertSame(3, $lookupAttempts);
        $this->assertSame([
            $externalId,
            $externalId,
            'WD-' . $withdrawal->id,
        ], $lookupExternalIds);
        Http::assertSent(function ($request) use ($withdrawal) {
            return $request->method() === 'POST'
                && $request->hasHeader('X-IDEMPOTENCY-KEY', 'clipkeo-testing-wd-' . $withdrawal->id);
        });
    }

    public function test_definite_xendit_rejection_refunds_the_full_amount_and_marks_fee_failed(): void
    {
        [$admin, $withdrawal, $transaction, $fee] = $this->createWithdrawal();

        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response([], 200);
            }

            return Http::response([
                'error_code' => 'RECIPIENT_ACCOUNT_NUMBER_ERROR',
                'message' => 'Invalid account number',
            ], 400);
        });

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame(50000.0, (float) $withdrawal->user->fresh()->wallet_balance);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertSame('failed', $transaction->fresh()->status);
        $this->assertSame('failed', $fee->fresh()->status);

        $this->assertDatabaseHas('transactions', [
            'reference_id' => 'WRF-WD-' . $withdrawal->id,
            'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
            'amount' => 50000,
            'status' => 'success',
        ]);
    }

    public function test_admin_rejection_refunds_gross_amount_and_does_not_refund_twice(): void
    {
        [$admin, $withdrawal, $transaction, $fee] = $this->createWithdrawal();

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.reject', $withdrawal->id), [
                'reason' => 'Dokumen rekening tidak valid',
            ])
            ->assertRedirect();

        $this->assertSame(50000.0, (float) $withdrawal->user->fresh()->wallet_balance);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertSame('failed', $transaction->fresh()->status);
        $this->assertSame('failed', $fee->fresh()->status);
        $this->assertDatabaseHas('transactions', [
            'reference_id' => 'WRF-WD-' . $withdrawal->id,
            'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
            'amount' => 50000,
            'status' => 'success',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.reject', $withdrawal->id), [
                'reason' => 'Percobaan duplikat',
            ])
            ->assertRedirect();

        $this->assertSame(50000.0, (float) $withdrawal->user->fresh()->wallet_balance);
        $this->assertDatabaseCount('transactions', 3);
    }

    public function test_processing_legacy_withdrawal_uses_its_original_external_id_for_status_check(): void
    {
        [$admin, $withdrawal, $transaction] = $this->createWithdrawal();
        $legacyExternalId = 'WD-20261127-' . $withdrawal->id;

        $withdrawal->update(['status' => 'processing']);
        $transaction->update(['reference_id' => $legacyExternalId]);

        Http::fake(function ($request) use ($legacyExternalId) {
            if ($request->method() === 'GET') {
                return Http::response([[
                    'id' => 'legacy-xendit-disbursement',
                    'external_id' => $legacyExternalId,
                    'status' => 'PENDING',
                ]], 200);
            }

            throw new \RuntimeException('A legacy payout must never be submitted again.');
        });

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame($legacyExternalId, $transaction->fresh()->reference_id);
        $this->assertSame('legacy-xendit-disbursement', $transaction->fresh()->external_ref);
        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($legacyExternalId) {
            return $request->method() === 'GET'
                && $request['external_id'] === $legacyExternalId;
        });
    }

    public function test_processing_withdrawal_checks_the_older_simple_external_id_before_retrying(): void
    {
        [$admin, $withdrawal, $transaction] = $this->createWithdrawal();
        $withdrawal->update(['status' => 'processing']);
        $simpleExternalId = 'WD-' . $withdrawal->id;

        Http::fake(function ($request) use ($simpleExternalId) {
            if ($request->method() === 'GET') {
                if ($request['external_id'] === $simpleExternalId) {
                    return Http::response([[
                        'id' => 'older-simple-xendit-disbursement',
                        'external_id' => $simpleExternalId,
                        'status' => 'PENDING',
                    ]], 200);
                }

                return Http::response([], 200);
            }

            throw new \RuntimeException('Existing simple-reference payout must not be sent again.');
        });

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame($simpleExternalId, $transaction->fresh()->reference_id);
        $this->assertSame('older-simple-xendit-disbursement', $transaction->fresh()->external_ref);
        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($simpleExternalId) {
            return $request->method() === 'GET'
                && $request['external_id'] === $simpleExternalId;
        });
    }

    public function test_processing_legacy_withdrawal_without_ledger_is_held_for_manual_reconciliation(): void
    {
        [$admin, $withdrawal, $transaction] = $this->createWithdrawal();
        $withdrawal->update(['status' => 'processing']);
        $transaction->delete();

        Http::fake();

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('processing', $withdrawal->fresh()->status);
        Http::assertNothingSent();
    }

    private function createWithdrawal(): array
    {
        $user = User::factory()->create([
            'role' => 'clipper',
            'email_verified_at' => now(),
            'security_pin' => Hash::make('123456'),
            'wallet_balance' => 0,
        ]);

        $method = $user->payoutMethods()->create([
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'is_primary' => true,
        ]);

        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'payout_method_id' => $method->id,
            'amount_requested' => 50000,
            'admin_fee' => 5000,
            'amount_final' => 45000,
            'status' => 'pending',
            'bank_info' => 'BCA (1234567890) a/n Test Account',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'withdrawal_request_id' => $withdrawal->id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'amount' => 45000,
            'currency' => 'IDR',
            'description' => 'Penarikan ke rekening tujuan',
            'reference_id' => 'WD-' . $withdrawal->id,
            'status' => 'pending',
        ]);

        $fee = Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_PLATFORM_FEE,
            'amount' => 5000,
            'currency' => 'IDR',
            'description' => 'Biaya admin withdrawal (10%)',
            'reference_id' => 'FEE-WD-' . $withdrawal->id,
            'status' => 'pending',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'security_pin' => Hash::make('123456'),
        ]);

        return [$admin, $withdrawal, $transaction, $fee];
    }
}

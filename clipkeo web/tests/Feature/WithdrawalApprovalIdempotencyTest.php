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

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
        Notification::fake();
        Mail::fake();
    }

    public function test_admin_approval_uses_one_stable_idempotency_key_and_does_not_resubmit_after_acceptance(): void
    {
        [$admin, $withdrawal, $transaction, $fee] = $this->createWithdrawal();

        Http::fake([
            'https://api.xendit.co/disbursements' => Http::response([
                'id' => 'xendit-disbursement-123',
                'external_id' => 'WD-' . $withdrawal->id,
                'status' => 'PENDING',
            ], 200),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        $this->assertSame('processing', $withdrawal->fresh()->status);
        $this->assertSame('xendit-disbursement-123', $transaction->fresh()->external_ref);
        $this->assertSame('pending', $fee->fresh()->status);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($withdrawal) {
            return $request->hasHeader('X-IDEMPOTENCY-KEY', 'WD-' . $withdrawal->id)
                && $request['external_id'] === 'WD-' . $withdrawal->id;
        });

        // A second admin click must not create another transfer after Xendit
        // has returned a provider disbursement ID.
        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal->id))
            ->assertRedirect();

        Http::assertSentCount(1);
    }

    public function test_admin_can_retry_an_ambiguous_timeout_with_the_same_idempotency_key(): void
    {
        [$admin, $withdrawal, $transaction] = $this->createWithdrawal();
        $attempts = 0;

        Http::fake(function ($request) use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                throw new ConnectionException('Simulated network timeout');
            }

            return Http::response([
                'id' => 'xendit-disbursement-after-retry',
                'external_id' => $request['external_id'],
                'status' => 'PENDING',
            ], 200);
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
            'xendit-disbursement-after-retry',
            $transaction->fresh()->external_ref
        );

        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($withdrawal) {
            return $request->hasHeader('X-IDEMPOTENCY-KEY', 'WD-' . $withdrawal->id);
        });
    }

    public function test_definite_xendit_rejection_refunds_the_full_amount_and_marks_fee_failed(): void
    {
        [$admin, $withdrawal, $transaction, $fee] = $this->createWithdrawal();

        Http::fake([
            'https://api.xendit.co/disbursements' => Http::response([
                'error_code' => 'RECIPIENT_ACCOUNT_NUMBER_ERROR',
                'message' => 'Invalid account number',
            ], 400),
        ]);

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

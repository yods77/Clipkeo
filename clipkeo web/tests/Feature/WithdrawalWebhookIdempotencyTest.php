<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WithdrawalWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.verification_token' => 'test-xendit-callback-token']);
        Notification::fake();
    }

    public function test_duplicate_failed_webhooks_and_late_completed_webhook_do_not_refund_twice(): void
    {
        [$user, $withdrawal, $transaction, $externalId] = $this->createPendingWithdrawal();

        $this->sendWebhook($externalId, 'FAILED')
            ->assertOk();

        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);
        $this->assertSame('failed', $transaction->fresh()->status);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertSame('failed', Transaction::where('reference_id', 'FEE-WD-' . $withdrawal->id)->value('status'));
        $this->assertDatabaseHas('transactions', [
            'reference_id' => 'WRF-WD-' . $withdrawal->id,
            'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
            'amount' => 50000,
            'status' => 'success',
        ]);

        $this->sendWebhook($externalId, 'FAILED')
            ->assertOk();

        $this->sendWebhook($externalId, 'COMPLETED')
            ->assertOk();

        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);
        $this->assertSame('failed', $transaction->fresh()->status);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
    }

    public function test_failed_webhook_after_completed_withdrawal_does_not_refund_wallet(): void
    {
        [$user, $withdrawal, $transaction, $externalId] = $this->createPendingWithdrawal();

        $this->sendWebhook($externalId, 'COMPLETED')
            ->assertOk();

        $this->assertSame(0.0, (float) $user->fresh()->wallet_balance);
        $this->assertSame('success', $transaction->fresh()->status);
        $this->assertSame('completed', $withdrawal->fresh()->status);

        $this->sendWebhook($externalId, 'FAILED')
            ->assertOk();

        $this->assertSame(0.0, (float) $user->fresh()->wallet_balance);
        $this->assertSame('success', $transaction->fresh()->status);
        $this->assertSame('completed', $withdrawal->fresh()->status);
    }

    private function createPendingWithdrawal(): array
    {
        $user = User::factory()->create([
            'wallet_balance' => 50000,
        ]);

        $payoutMethod = $user->payoutMethods()->create([
            'bank_name' => 'Bank Test',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'is_primary' => true,
        ]);

        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount_requested' => 50000,
            'amount_final' => 45000,
            'admin_fee' => 5000,
            'payout_method_id' => $payoutMethod->id,
            'status' => 'processing',
            'bank_info' => 'Bank Test (1234567890) a/n Test Account',
        ]);

        $externalId = 'WD-' . $withdrawal->id;

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'withdrawal_request_id' => $withdrawal->id,
            'amount' => 45000,
            'currency' => 'IDR',
            'description' => 'Penarikan sedang diproses',
            'reference_id' => $externalId,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_PLATFORM_FEE,
            'amount' => 5000,
            'currency' => 'IDR',
            'description' => 'Biaya admin withdrawal (10%)',
            'reference_id' => 'FEE-WD-' . $withdrawal->id,
            'status' => 'pending',
        ]);

        return [$user, $withdrawal, $transaction, $externalId];
    }

    private function sendWebhook(string $externalId, string $status)
    {
        return $this->postJson('/webhooks/xendit', [
            'external_id' => $externalId,
            'status' => $status,
            'failure_code' => 'TEST_FAILURE',
        ], [
            'x-callback-token' => 'test-xendit-callback-token',
        ]);
    }
}

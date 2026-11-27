<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WalletWithdrawalIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function createWithdrawalUser(float $balance = 100000): array
    {
        $user = User::factory()->create([
            'role' => 'clipper',
            'wallet_balance' => $balance,
            'security_pin' => Hash::make('123456'),
        ]);

        $payoutMethod = $user->payoutMethods()->create([
            'bank_name' => 'Bank Test',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'is_primary' => true,
        ]);

        $token = $user->createToken('withdraw-idempotency-test')->plainTextToken;

        return [$user, $payoutMethod, ['Authorization' => 'Bearer ' . $token]];
    }

    public function test_retry_with_the_same_key_returns_the_original_withdrawal_without_a_second_debit(): void
    {
        [$user, $payoutMethod, $headers] = $this->createWithdrawalUser();
        $headers['Idempotency-Key'] = '22222222-2222-4222-8222-222222222222';

        $payload = [
            'amount' => 50000,
            'payout_method_id' => $payoutMethod->id,
            'pin' => '123456',
        ];

        $firstResponse = $this->postJson('/api/wallet/withdraw', $payload, $headers)
            ->assertOk()
            ->assertJsonPath('data.idempotent_replay', false);

        $withdrawalId = $firstResponse->json('data.wd_id');

        $this->postJson('/api/wallet/withdraw', $payload, $headers)
            ->assertOk()
            ->assertJsonPath('data.wd_id', $withdrawalId)
            ->assertJsonPath('data.idempotent_replay', true);

        $this->assertDatabaseCount('withdrawal_requests', 1);
        $this->assertDatabaseCount('transactions', 2);
        $this->assertSame(50000.0, (float) $user->fresh()->wallet_balance);
    }

    public function test_reusing_a_key_with_different_withdrawal_details_is_rejected(): void
    {
        [$user, $payoutMethod, $headers] = $this->createWithdrawalUser();
        $headers['Idempotency-Key'] = '33333333-3333-4333-8333-333333333333';

        $this->postJson('/api/wallet/withdraw', [
            'amount' => 50000,
            'payout_method_id' => $payoutMethod->id,
            'pin' => '123456',
        ], $headers)->assertOk();

        $this->postJson('/api/wallet/withdraw', [
            'amount' => 60000,
            'payout_method_id' => $payoutMethod->id,
            'pin' => '123456',
        ], $headers)->assertStatus(409);

        $this->assertDatabaseCount('withdrawal_requests', 1);
        $this->assertDatabaseCount('transactions', 2);
        $this->assertSame(50000.0, (float) $user->fresh()->wallet_balance);
    }

    public function test_withdrawal_requires_an_idempotency_key(): void
    {
        [$user, $payoutMethod, $headers] = $this->createWithdrawalUser();

        $this->postJson('/api/wallet/withdraw', [
            'amount' => 50000,
            'payout_method_id' => $payoutMethod->id,
            'pin' => '123456',
        ], $headers)->assertUnprocessable();

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);
    }

    public function test_the_same_key_can_be_used_by_different_users_without_cross_account_replay(): void
    {
        [$firstUser, $firstPayoutMethod, $firstHeaders] = $this->createWithdrawalUser();
        [$secondUser, $secondPayoutMethod, $secondHeaders] = $this->createWithdrawalUser();

        $key = '44444444-4444-4444-8444-444444444444';
        $firstHeaders['Idempotency-Key'] = $key;
        $secondHeaders['Idempotency-Key'] = $key;

        $firstResponse = $this->postJson('/api/wallet/withdraw', [
            'amount' => 50000,
            'payout_method_id' => $firstPayoutMethod->id,
            'pin' => '123456',
        ], $firstHeaders)->assertOk();

        $secondResponse = $this->postJson('/api/wallet/withdraw', [
            'amount' => 50000,
            'payout_method_id' => $secondPayoutMethod->id,
            'pin' => '123456',
        ], $secondHeaders)->assertOk();

        $this->assertNotSame(
            $firstResponse->json('data.wd_id'),
            $secondResponse->json('data.wd_id')
        );
        $this->assertDatabaseCount('withdrawal_requests', 2);
        $this->assertSame(50000.0, (float) $firstUser->fresh()->wallet_balance);
        $this->assertSame(50000.0, (float) $secondUser->fresh()->wallet_balance);
    }
}

<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WalletWithdrawalPinTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_withdrawal_requires_the_correct_server_verified_pin(): void
    {
        $user = User::factory()->create([
            'role' => 'clipper',
            'wallet_balance' => 100000,
            'security_pin' => Hash::make('123456'),
        ]);

        $payoutMethod = $user->payoutMethods()->create([
            'bank_name' => 'Bank Test',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'is_primary' => true,
        ]);

        $token = $user->createToken('withdraw-pin-test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];
        $payload = [
            'amount' => 50000,
            'payout_method_id' => $payoutMethod->id,
        ];

        $this->postJson('/api/wallet/withdraw', $payload, $headers)
            ->assertUnprocessable();

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);

        $this->postJson('/api/wallet/withdraw', $payload + ['pin' => '000000'], $headers)
            ->assertUnauthorized();

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);

        $this->postJson('/api/wallet/withdraw', $payload + ['pin' => '123456'], $headers)
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount_requested' => 50000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $this->assertSame(50000.0, (float) $user->fresh()->wallet_balance);
    }
}

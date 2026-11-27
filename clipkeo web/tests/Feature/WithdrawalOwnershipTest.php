<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WithdrawalOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_user_cannot_withdraw_to_another_users_payout_method(): void
    {
        $user = $this->createClipper();
        $otherUser = $this->createClipper();

        $otherBank = $otherUser->payoutMethods()->create([
            'bank_name' => 'Bank Other',
            'account_number' => '9999999999',
            'account_name' => 'Other Account',
            'is_primary' => true,
        ]);

        $this->actingAs($user)
            ->post(route('withdrawals.store'), [
                'amount' => 50000,
                'payout_method_id' => $otherBank->id,
                'pin' => '123456',
            ])
            ->assertSessionHasErrors('payout_method_id');

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertSame(100000.0, (float) $user->fresh()->wallet_balance);
    }

    public function test_web_withdrawal_creates_request_and_debits_balance_atomically(): void
    {
        $user = $this->createClipper();

        $bank = $user->payoutMethods()->create([
            'bank_name' => 'Bank Test',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'is_primary' => true,
        ]);

        $this->actingAs($user)
            ->post(route('withdrawals.store'), [
                'amount' => 50000,
                'payout_method_id' => $bank->id,
                'pin' => '123456',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'payout_method_id' => $bank->id,
            'amount_requested' => 50000,
            'amount_final' => 45000,
            'status' => 'pending',
        ]);

        $this->assertSame(50000.0, (float) $user->fresh()->wallet_balance);
    }

    private function createClipper(): User
    {
        return User::factory()->create([
            'role' => 'clipper',
            'email_verified_at' => now(),
            'security_pin' => Hash::make('123456'),
            'wallet_balance' => 100000,
        ]);
    }
}

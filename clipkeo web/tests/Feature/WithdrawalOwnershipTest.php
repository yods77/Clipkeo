<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WithdrawalRequest;
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

        $withdrawal = \\App\\Models\\WithdrawalRequest::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(5000.0, (float) $withdrawal->admin_fee);
        $this->assertDatabaseHas('transactions', [
            'withdrawal_request_id' => $withdrawal->id,
            'type' => 'withdrawal',
            'amount' => 45000,
            'reference_id' => 'WD-' . $withdrawal->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('transactions', [
            'reference_id' => 'FEE-WD-' . $withdrawal->id,
            'type' => 'platform_fee',
            'amount' => 5000,
            'status' => 'pending',
        ]);
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

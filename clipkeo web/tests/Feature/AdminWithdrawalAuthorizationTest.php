<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWithdrawalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_approve_or_reject_withdrawals(): void
    {
        $clipper = User::factory()->create([
            'role' => 'clipper',
            'email_verified_at' => now(),
            'security_pin' => Hash::make('123456'),
        ]);

        $this->actingAs($clipper)
            ->post('/admin/withdrawals/999/approve')
            ->assertForbidden();

        $this->actingAs($clipper)
            ->post('/admin/withdrawals/999/reject')
            ->assertForbidden();
    }
}

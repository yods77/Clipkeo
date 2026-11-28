<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BountyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_clipper_cannot_open_campaign_creation_form(): void
    {
        $clipper = $this->createUserWithRole('clipper');

        $this->actingAs($clipper)
            ->get(route('bounties.create'))
            ->assertForbidden();
    }

    public function test_clipper_cannot_submit_campaign_creation_request(): void
    {
        $clipper = $this->createUserWithRole('clipper', [
            'wallet_balance' => 100000,
        ]);

        $this->actingAs($clipper)
            ->post(route('bounties.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('bounties', 0);
        $this->assertSame(100000.0, (float) $clipper->fresh()->wallet_balance);
    }

    public function test_admin_cannot_use_publisher_campaign_creation_form(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('bounties.create'))
            ->assertForbidden();
    }

    public function test_publisher_can_open_campaign_creation_form(): void
    {
        $publisher = $this->createUserWithRole('publisher');

        $this->actingAs($publisher)
            ->get(route('bounties.create'))
            ->assertOk();
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'email_verified_at' => now(),
            'security_pin' => Hash::make('123456'),
        ], $attributes));
    }
}

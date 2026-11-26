<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create([
            'username' => 'security-' . Str::lower(Str::random(10)),
        ]);
    }

    public function test_api_login_rejects_invalid_credentials_with_a_generic_message(): void
    {
        $user = $this->createUser();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password tidak valid.',
            ]);
    }

    public function test_google_login_does_not_trust_client_supplied_identity_fields(): void
    {
        $this->postJson('/api/login-google', [
            'email' => 'victim@example.com',
            'google_id' => 'forged-google-id',
            'name' => 'Impersonator',
        ])->assertStatus(422);
    }

    public function test_expired_password_reset_otp_is_rejected_and_deleted(): void
    {
        $user = $this->createUser();

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(400);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_successful_password_reset_revokes_existing_api_tokens(): void
    {
        $user = $this->createUser();
        $user->createToken('existing-session');

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_expired_pin_reset_otp_is_rejected_and_deleted(): void
    {
        $user = $this->createUser();

        DB::table('pin_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->postJson('/api/reset-pin', [
            'email' => $user->email,
            'otp' => '123456',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ])->assertStatus(400);

        $this->assertDatabaseMissing('pin_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_pin_otp_verification_is_limited_after_five_incorrect_attempts(): void
    {
        $user = $this->createUser();

        DB::table('pin_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/verify-pin-otp', [
                'email' => $user->email,
                'otp' => '000000',
            ])->assertStatus(400);
        }

        $this->postJson('/api/verify-pin-otp', [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertStatus(429);
    }
}

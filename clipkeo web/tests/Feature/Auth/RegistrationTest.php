<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        DB::table('provinces')->insert([
            'id' => '31',
            'name' => 'DKI Jakarta',
        ]);

        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Nomor HP')
            ->assertSee('Jenis Kelamin')
            ->assertSee('Tanggal Lahir')
            ->assertSee('DKI Jakarta');
    }

    public function test_regency_endpoint_returns_only_regencies_for_the_selected_province(): void
    {
        DB::table('provinces')->insert([
            ['id' => '31', 'name' => 'DKI Jakarta'],
            ['id' => '32', 'name' => 'Jawa Barat'],
        ]);

        DB::table('regencies')->insert([
            ['id' => '3171', 'province_id' => '31', 'name' => 'Jakarta Selatan'],
            ['id' => '3201', 'province_id' => '32', 'name' => 'Kabupaten Bogor'],
        ]);

        $this->getJson('/register/regencies/31')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => '3171',
                'name' => 'Jakarta Selatan',
            ])
            ->assertJsonMissing([
                'id' => '3201',
                'name' => 'Kabupaten Bogor',
            ]);
    }

    public function test_new_users_can_register_with_all_required_profile_details(): void
    {
        DB::table('provinces')->insert([
            'id' => '31',
            'name' => 'DKI Jakarta',
        ]);

        DB::table('regencies')->insert([
            'id' => '3171',
            'province_id' => '31',
            'name' => 'Jakarta Selatan',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'clipper',
            'gender' => 'Laki-laki',
            'birthday' => '1995-01-01',
            'province' => '31',
            'city' => '3171',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone_number' => '081234567890',
            'role' => 'clipper',
            'gender' => 'Laki-laki',
            'birthday' => '1995-01-01',
            'province_id' => '31',
            'city_id' => '3171',
        ]);
    }

    public function test_new_users_can_register_as_a_publisher(): void
    {
        DB::table('provinces')->insert([
            'id' => '31',
            'name' => 'DKI Jakarta',
        ]);

        DB::table('regencies')->insert([
            'id' => '3171',
            'province_id' => '31',
            'name' => 'Jakarta Selatan',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test Publisher',
            'phone_number' => '081234567891',
            'email' => 'publisher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'publisher',
            'gender' => 'Laki-laki',
            'birthday' => '1990-01-01',
            'province' => '31',
            'city' => '3171',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'publisher@example.com',
            'role' => 'publisher',
            'province_id' => '31',
            'city_id' => '3171',
        ]);
    }

    public function test_registration_requires_all_profile_details(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'required-fields@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'clipper',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'phone_number',
                'gender',
                'birthday',
                'province',
                'city',
            ]);

        $this->assertGuest();
    }

    public function test_city_must_belong_to_the_selected_province(): void
    {
        DB::table('provinces')->insert([
            ['id' => '31', 'name' => 'DKI Jakarta'],
            ['id' => '32', 'name' => 'Jawa Barat'],
        ]);

        DB::table('regencies')->insert([
            'id' => '3171',
            'province_id' => '31',
            'name' => 'Jakarta Selatan',
        ]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'email' => 'wrong-region@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'clipper',
            'gender' => 'Laki-laki',
            'birthday' => '1995-01-01',
            'province' => '32',
            'city' => '3171',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('city');

        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'email' => 'pemohon_test@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('pemohon');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_login_via_sanctum_api()
    {
        $user = User::factory()->create([
            'email' => 'api_test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'access_token', 'token_type', 'user']);
    }

    public function test_user_can_register_via_sanctum_api()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'API Pemohon',
            'email' => 'api_register@example.com',
            'phone' => '08123456789',
            'nip_nik' => '3171000011112222',
            'company_name' => 'PT API Register',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
                 ->assertJsonStructure(['status', 'access_token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'api_register@example.com',
            'company_name' => 'PT API Register',
        ]);
    }
}

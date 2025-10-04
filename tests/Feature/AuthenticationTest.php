<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_service()
    {
        $service = Service::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0123456789',
            'service_id' => $service->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->role);
        $this->assertEquals($service->id, $user->service_id);
        $this->assertTrue($user->is_active);
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $service = Service::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'service_id' => $service->id,
            'role' => 'employee',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}

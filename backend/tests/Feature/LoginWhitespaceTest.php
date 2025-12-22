<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginWhitespaceTest extends TestCase
{
  use RefreshDatabase;

    public function test_user_cannot_login_with_leading_or_trailing_whitespace()
    {
        $password = 'password123';
        User::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'first_name' => 'Test',
            'last_name' => 'User',
            'role' => 'STUDENT',
            'is_active' => true,
        ]);

        // Case 1: Login with leading whitespace => Should fail
        $response = $this->postJson('/api/auth/login', [
            'login' => ' test@example.com',
            'password' => $password,
        ]);
        $response->assertStatus(401);

        // Case 2: Login with trailing whitespace => Should fail
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com ',
            'password' => $password,
        ]);
        $response->assertStatus(401);

        // Case 3: Login without whitespace => Should succeed
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => $password,
        ]);
        // Note: The original AuthController returns 200 on success.
        $response->assertStatus(200);
    }
}

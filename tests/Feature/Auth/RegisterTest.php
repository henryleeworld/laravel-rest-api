<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
            'device_name' => 'iphone-15',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'test@example.com');

        $user = User::firstWhere(
            'email',
            'test@example.com'
        );

        $this->assertNotNull($user);
        $this->assertSame(1, $user->tokens()->count());
        $this->assertSame(
            'iphone-15',
            $user->tokens()->first()->name
        );
    }

    public function test_the_token_name_defaults_to_api_when_device_name_is_omitted(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
        ])->assertCreated();

        $user = User::firstWhere(
            'email',
            'test@example.com'
        );

        $this->assertSame(
            'api',
            $user->tokens()->first()->name
        );
    }

    public function test_the_email_is_normalized_to_lowercase(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'Test@EXAMPLE.com',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
        ])->assertCreated();

        $this->assertNotNull(
            User::firstWhere('email', 'test@example.com')
        );
    }

    public function test_registration_fails_with_a_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_registration_fails_with_a_weak_password(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function seedResetCode(
        string $email,
        string $code,
        ?DateTimeInterface $createdAt = null
    ): void {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => $createdAt ?? now(),
            ]
        );
    }

    public function test_a_valid_code_resets_the_password_and_revokes_all_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $user->createToken('api');
        $user->createToken('cli');

        $this->seedResetCode(
            'test@example.com',
            '123456'
        );

        $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                __('Password has been reset.')
            );

        $this->assertTrue(
            Hash::check(
                'new-secret-password',
                $user->fresh()->password
            )
        );

        $this->assertFalse(
            DB::table('password_reset_tokens')
                ->where('email', 'test@example.com')
                ->exists()
        );

        $this->assertSame(
            0,
            $user->tokens()->count()
        );
    }

    public function test_a_wrong_code_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->seedResetCode(
            'test@example.com',
            '123456'
        );

        $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'code' => '654321',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertFalse(
            Hash::check(
                'new-secret-password',
                $user->fresh()->password
            )
        );
    }

    public function test_an_expired_code_is_rejected_and_deleted(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->seedResetCode(
            'test@example.com',
            '123456',
            now()->subMinutes(16)
        );

        $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertFalse(
            DB::table('password_reset_tokens')
                ->where('email', 'test@example.com')
                ->exists()
        );
    }

    public function test_a_code_cannot_be_used_twice(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->seedResetCode(
            'test@example.com',
            '123456'
        );

        $payload = [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ];

        $this->postJson(
            '/api/v1/reset-password',
            $payload
        )->assertOk();

        $this->postJson(
            '/api/v1/reset-password',
            $payload
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
    }

    public function test_an_email_without_a_pending_code_is_rejected_with_the_same_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
    }
}

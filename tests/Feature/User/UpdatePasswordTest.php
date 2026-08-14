<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_password_can_be_changed_and_other_tokens_are_revoked(): void
    {
        $user = User::factory()->create();

        $currentToken = $user->createToken('current')->plainTextToken;

        $user->createToken('other');

        $this->withToken($currentToken)
            ->putJson('/api/v1/user/password', [
                'current_password' => 'password',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', __('Password updated.'));

        $this->assertTrue(
            Hash::check(
                'new-secret-password',
                $user->fresh()->password
            )
        );

        $this->assertSame(
            ['current'],
            $user->tokens()->pluck('name')->all()
        );

        $this->withToken($currentToken)
            ->getJson('/api/v1/user')
            ->assertOk();
    }

    public function test_the_current_password_must_be_correct(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/user/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_changing_the_password_requires_authentication(): void
    {
        $this->putJson('/api/v1/user/password', [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertUnauthorized();
    }
}

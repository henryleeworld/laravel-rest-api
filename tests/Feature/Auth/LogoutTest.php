<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertNoContent();

        $this->assertSame(
            0,
            $user->tokens()->count()
        );

        auth()->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/user')
            ->assertUnauthorized();
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/logout')
            ->assertUnauthorized();
    }
}

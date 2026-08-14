<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_active_tokens_can_be_listed_without_exposing_hashes(): void
    {
        $user = User::factory()->create();

        $user->createToken('iphone-15');
        $user->createToken('cli');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/tokens')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'abilities',
                    'last_used_at',
                    'created_at',
                ]],
            ])
            ->assertJsonMissingPath('data.0.token');
    }

    public function test_a_token_can_be_revoked_by_id(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api');

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/v1/tokens/{$token->accessToken->id}"
        )->assertNoContent();

        $this->assertSame(
            0,
            $user->tokens()->count()
        );
    }

    public function test_revoking_another_users_token_returns_404(): void
    {
        $otherToken = User::factory()
            ->create()
            ->createToken('api');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/v1/tokens/{$otherToken->accessToken->id}"
        )->assertNotFound();

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
    }

    public function test_all_tokens_except_the_current_one_can_be_revoked(): void
    {
        $user = User::factory()->create();

        $currentToken = $user->createToken('current')->plainTextToken;

        $user->createToken('other');
        $user->createToken('another');

        $this->withToken($currentToken)
            ->deleteJson('/api/v1/tokens')
            ->assertNoContent();

        $this->assertSame(
            ['current'],
            $user->tokens()->pluck('name')->all()
        );

        $this->withToken($currentToken)
            ->getJson('/api/v1/user')
            ->assertOk();
    }

    public function test_listing_tokens_requires_authentication(): void
    {
        $this->getJson('/api/v1/tokens')
            ->assertUnauthorized();
    }
}

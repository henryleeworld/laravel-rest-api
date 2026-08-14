<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_authenticated_user_can_be_retrieved(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');
    }

    public function test_the_user_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/user')
            ->assertUnauthorized();
    }

    public function test_the_profile_name_and_email_can_be_updated(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/user', [
            'name' => 'New Name',
            'email' => 'New@Example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com');

        $freshUser = $user->fresh();

        $this->assertSame('New Name', $freshUser->name);
        $this->assertSame('new@example.com', $freshUser->email);
    }

    public function test_a_partial_update_only_changes_the_provided_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/user', [
            'name' => 'New Name',
        ])->assertOk();

        $freshUser = $user->fresh();

        $this->assertSame('New Name', $freshUser->name);
        $this->assertSame($user->email, $freshUser->email);
    }

    public function test_the_email_must_be_unique_to_other_users(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/user', [
            'email' => 'taken@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_the_user_can_keep_their_own_email(): void
    {
        $user = User::factory()->create([
            'email' => 'mine@example.com',
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/user', [
            'email' => 'mine@example.com',
        ])->assertOk();
    }
}

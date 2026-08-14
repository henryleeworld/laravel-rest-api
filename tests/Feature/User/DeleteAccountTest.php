<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_account_can_be_deleted_with_a_password_confirmation(): void
    {
        $user = User::factory()->create();

        $user->createToken('api');

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/user', [
            'password' => 'password',
        ])
            ->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_the_password_must_be_correct_to_delete_the_account(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/user', [
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_deleting_the_account_requires_authentication(): void
    {
        $this->deleteJson('/api/v1/user', [
            'password' => 'password',
        ])->assertUnauthorized();
    }
}

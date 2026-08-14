<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_reset_code_is_emailed_and_stored_hashed(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                __('If the email exists, a reset code has been sent.')
            );

        $row = DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first();

        $this->assertNotNull($row);

        Notification::assertSentTo(
            $user,
            PasswordResetCode::class,
            function (PasswordResetCode $notification) use ($row): bool {
                return strlen($notification->code) === 6
                    && Hash::check($notification->code, $row->token);
            }
        );
    }

    public function test_an_unknown_email_gets_the_same_response_and_no_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'missing@example.com',
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                __('If the email exists, a reset code has been sent.')
            );

        Notification::assertNothingSent();

        $this->assertSame(
            0,
            DB::table('password_reset_tokens')->count()
        );
    }

    public function test_requesting_a_new_code_replaces_the_previous_one(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ])->assertOk();

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ])->assertOk();

        $this->assertSame(
            1,
            DB::table('password_reset_tokens')
                ->where('email', 'test@example.com')
                ->count()
        );
    }
}

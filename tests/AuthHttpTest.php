<?php

namespace ShabuShabu\Tightrope\Tests;

use Illuminate\Auth\Events\{Registered, Verified};
use Illuminate\Auth\Notifications\{ResetPassword, VerifyEmail};
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\{Event, Notification, Password};
use Orchestra\Testbench\TestCase;
use ShabuShabu\Tightrope\Tests\App\User;
use ShabuShabu\Tightrope\Tests\Support\AppSetup;

/**
 * @todo Test for these events:
 * Illuminate\Auth\Events\PasswordReset;
 * Illuminate\Auth\Events\Attempting;
 * Illuminate\Auth\Events\Registered;
 * Illuminate\Auth\Events\Verified;
 * Illuminate\Auth\Events\Logout;
 * Illuminate\Auth\Events\Login;
 * @todo Override these requests:
 * ShabuShabu\Tightrope\Http\Requests\EmailPasswordRequest;
 * ShabuShabu\Tightrope\Http\Requests\ResetPasswordRequest;
 */
class AuthHttpTest extends TestCase
{
    use AppSetup;

    /**
     * @test
     */
    public function ensure_that_a_user_can_log_in_and_out(): void
    {
        $user = factory(User::class)->create();

        $response = $this->postJson('login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertCookieMissing(config('tightrope.refresh_cookie_name'))
                 ->assertJsonStructure([
                     'accessToken',
                     'refreshToken',
                     'tokenType',
                     'expiresIn',
                 ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response->json('accessToken'),
        ];

        $this->getJson('me', $headers)
             ->assertStatus(Response::HTTP_OK);

        $this->postJson('logout', [], $headers)
             ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $user->id,
            'revoked' => true,
        ])->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $user->id,
            'revoked' => false,
        ])->assertDatabaseHas('oauth_refresh_tokens', [
            'access_token_id' => request()->user()->token()->id,
            'revoked'         => true,
        ]);
    }

    /**
     * @test
     */
    public function ensure_that_an_anonymous_user_can_register(): void
    {
        Event::fake();

        $response = $this->postJson('register', [
            'email'                => $email = 'test@test.com',
            'password'             => $pw = 'supersecretpassword',
            'passwordConfirmation' => $pw,
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertHeaderMissing('Location')
                 ->assertHeader('X-Request-ID');

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        Event::assertDispatched(Registered::class, fn($e) => $e->user->id === $response->headers->get('X-Request-ID'));
    }

    /**
     * @test
     */
    public function ensure_that_an_authenticated_user_cannot_register(): void
    {
        Event::fake();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
                         ->postJson('register', [
                             'email'                 => $email = 'test@test.com',
                             'password'              => $pw = 'supersecretpassword',
                             'password_confirmation' => $pw,
                         ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);

        Event::assertNotDispatched(Registered::class);
    }

    /**
     * @test
     */
    public function ensure_that_the_verification_signature_gets_checked(): void
    {
        Event::fake();

        $user = factory(User::class)
            ->state('unverified')
            ->create();

        $response = $this->actingAs($user)
                         ->getJson('email/verify/' . $user->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEmpty($user->fresh()->email_verified_at);

        Event::assertNotDispatched(Verified::class);
    }

    /**
     * @test
     */
    public function ensure_that_an_unverified_supporter_can_verify(): void
    {
        Event::fake();

        $user = factory(User::class)
            ->state('unverified')
            ->create();

        $response = $this->actingAs($user)
                         ->withoutMiddleware(ValidateSignature::class)
                         ->getJson('email/verify/' . $user->id);

        $response->assertOk()
                 ->assertJson(['verified' => true]);

        $this->assertNotEmpty($user->fresh()->email_verified_at);

        Event::assertDispatched(Verified::class, fn($e) => $e->user->id === $user->id);
    }

    /**
     * @test
     */
    public function ensure_that_a_verified_supporter_cannot_verify(): void
    {
        Event::fake();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
                         ->withoutMiddleware(ValidateSignature::class)
                         ->getJson('email/verify/' . $user->id);

        $response->assertOk()
                 ->assertJson(['already_verified' => true]);

        $this->assertNotEmpty($user->fresh()->email_verified_at);

        Event::assertNotDispatched(Verified::class);
    }

    /**
     * @test
     */
    public function ensure_that_an_anonymous_user_cannot_verify(): void
    {
        Event::fake();

        $user = factory(User::class)
            ->state('unverified')
            ->create();

        $response = $this->getJson('email/verify/' . $user->id);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertEmpty($user->fresh()->email_verified_at);

        Event::assertNotDispatched(Verified::class);
    }

    /**
     * @test
     */
    public function ensure_that_an_unverified_user_cannot_verify_another_user(): void
    {
        Event::fake();

        $user = factory(User::class)
            ->state('unverified')
            ->create();

        $authorizedUser = factory(User::class)
            ->state('unverified')
            ->create();

        $response = $this->actingAs($authorizedUser)
                         ->getJson('email/verify/' . $user->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEmpty($user->fresh()->email_verified_at);

        Event::assertNotDispatched(Verified::class);
    }

    /**
     * @test
     */
    public function ensure_that_an_unverified_supporter_can_get_verification_resent(): void
    {
        Notification::fake();

        $user = factory(User::class)
            ->state('unverified')
            ->create();

        $response = $this->actingAs($user)
                         ->postJson('email/resend');

        $response->assertOk()
                 ->assertJson(['resent' => true]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function ensure_that_a_verified_supporter_cannot_get_verification_resent(): void
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
                         ->postJson('email/resend');

        $response->assertOk()
                 ->assertJson(['already_verified' => true]);

        Notification::assertNotSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function ensure_that_an_anonymous_user_cannot_get_verification_resent(): void
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $response = $this->postJson('email/resend');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        Notification::assertNotSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function ensure_that_a_user_can_reset_their_password(): void
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $this->postJson('password/request', [
            'email' => $user->email,
        ])
             ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);

        $this->postJson('password/reset', [
            'email'                 => $user->email,
            'password'              => $newPw = 'anothersecretpassword',
            'password_confirmation' => $newPw,
            'token'                 => Password::broker()->getRepository()->create($user),
        ])->assertOk();

        $this->assertTrue(auth('web')->once([
            'email'    => $user->email,
            'password' => $newPw,
        ]));
    }

    /**
     * @test
     */
    public function ensure_that_an_invalid_password_reset_causes_a_validation_error(): void
    {
        Notification::fake();

        $response = $this->postJson('password/request', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        Notification::assertNothingSent();
    }

    /**
     * @test
     */
    public function ensure_that_a_non_existent_email_address_causes_a_validation_error(): void
    {
        Notification::fake();

        $response = $this->postJson('password/request', [
            'email' => 'some@email.com',
        ]);

        $this->assertSame($response->getContent(), 'passwords.user');

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        Notification::assertNothingSent();
    }
}

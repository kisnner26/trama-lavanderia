<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_an_assigned_user_can_sign_in_with_a_normalized_email_and_a_new_session(): void
    {
        $password = 'private-test-passphrase';
        $membership = Membership::factory()->create(['user_id' => User::factory()->create(['email' => 'owner@example.test', 'password' => Hash::make($password)])->id]);
        Session::start();
        $oldId = Session::getId();

        $this->post(route('login.store'), ['email' => ' OWNER@EXAMPLE.TEST ', 'password' => $password])->assertRedirectToRoute('home');

        $this->assertAuthenticatedAs($membership->user);
        $this->assertNotSame($oldId, Session::getId());
    }

    public function test_an_unassigned_account_cannot_enter(): void
    {
        $user = User::factory()->create(['password' => Hash::make('unassigned-test-passphrase')]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'unassigned-test-passphrase'])
            ->assertSessionHasErrors(['email' => 'no pudimos abrir tu sesión. revisa tus credenciales.']);
        $this->assertGuest();
    }

    public function test_a_failed_login_does_not_flash_the_password(): void
    {
        $this->post(route('login.store'), ['email' => 'missing@example.test', 'password' => 'sensitive-test-password'])
            ->assertSessionHasErrors(['email' => 'no pudimos abrir tu sesión. revisa tus credenciales.'])
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_requires_both_credentials(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors([
            'email' => 'escribe tu correo.', 'password' => 'escribe tu contraseña.',
        ]);
    }

    public function test_repeated_failures_block_even_a_correct_password_temporarily(): void
    {
        $user = User::factory()->create(['email' => 'limited@example.test', 'password' => Hash::make('valid-test-passphrase')]);
        Membership::factory()->create(['user_id' => $user->id]);
        $key = 'login:'.hash('sha256', $user->email.'|127.0.0.1');
        for ($attempt = 0; $attempt < 5; $attempt++) {
            RateLimiter::hit($key, 60);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'valid-test-passphrase'])
            ->assertSessionHasErrors(['email' => 'demasiados intentos. vuelve a probar en un minuto.']);
        $this->assertGuest();
    }

    public static function invalidPasswords(): array
    {
        return [
            'más de 72 bytes' => [str_repeat('a', 73)],
            'unicode fuera del límite' => [str_repeat('á', 37)],
            'carácter nulo' => ["test-password\0suffix"],
        ];
    }

    #[DataProvider('invalidPasswords')]
    public function test_passwords_that_cannot_be_hashed_safely_are_rejected(string $password): void
    {
        $this->post(route('login.store'), ['email' => 'owner@example.test', 'password' => $password])
            ->assertSessionHasErrors(['password' => 'la contraseña es demasiado larga o contiene caracteres no válidos.']);
        $this->assertGuest();
    }

    public function test_a_unicode_password_at_the_byte_limit_can_sign_in(): void
    {
        $password = str_repeat('á', 36);
        $user = User::factory()->create(['password' => Hash::make($password)]);
        Membership::factory()->create(['user_id' => $user->id]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => $password])->assertRedirectToRoute('home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_closes_the_session_and_discards_its_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['private_context' => 'discard'])
            ->post(route('logout'))->assertRedirectToRoute('login')->assertSessionMissing('private_context');
        $this->assertGuest();
    }
}

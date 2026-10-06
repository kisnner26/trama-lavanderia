<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Membership;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProvisionBusinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_provisioning_creates_an_owner_and_hashes_the_prompted_password(): void
    {
        $this->artisan('trama:provision', [
            'email' => ' OWNER@EXAMPLE.TEST ', '--business' => 'lavandería de prueba',
            '--branch' => 'mostrador central', '--name' => 'responsable',
        ])->expectsQuestion('contraseña del propietario (mínimo 12 caracteres)', 'private-test-passphrase')->assertSuccessful();

        $membership = Membership::with('user', 'branch.business')->sole();
        $this->assertSame(Role::Owner, $membership->role);
        $this->assertSame('owner@example.test', $membership->user->email);
        $this->assertTrue(Hash::check('private-test-passphrase', $membership->user->password));
        $this->assertSame('lavandería de prueba', $membership->branch->business->name);
    }

    public function test_invalid_data_leaves_no_partial_business_or_account(): void
    {
        $this->artisan('trama:provision', [
            'email' => 'invalid', '--business' => 'lavandería de prueba',
            '--branch' => 'central', '--name' => 'responsable',
        ])->expectsQuestion('contraseña del propietario (mínimo 12 caracteres)', 'short')->assertFailed();

        $this->assertDatabaseCount('businesses', 0);
        $this->assertDatabaseCount('branches', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('memberships', 0);
    }

    public static function unsupportedPasswords(): array
    {
        return [
            'unicode fuera del límite' => [str_repeat('á', 37)],
            'carácter nulo' => ["test-password\0suffix"],
        ];
    }

    #[DataProvider('unsupportedPasswords')]
    public function test_unsupported_passwords_leave_no_business_or_owner(string $password): void
    {
        $this->artisan('trama:provision', [
            'email' => 'owner@example.test', '--business' => 'negocio de prueba',
            '--branch' => 'central', '--name' => 'responsable',
        ])->expectsQuestion('contraseña del propietario (mínimo 12 caracteres)', $password)->assertFailed();

        $this->assertDatabaseCount('businesses', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('memberships', 0);
    }
}

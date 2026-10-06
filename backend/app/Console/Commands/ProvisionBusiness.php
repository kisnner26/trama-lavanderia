<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Business;
use App\Models\Membership;
use App\Models\User;
use App\Rules\PasswordByteLimit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProvisionBusiness extends Command
{
    protected $signature = 'trama:provision {email} {--business=} {--branch=} {--name=} {--currency=NIO} {--timezone=America/Managua} {--password-stdin}';

    protected $description = 'crea un negocio, una sucursal y su propietario sin credenciales predeterminadas';

    public function handle(): int
    {
        if (! $this->option('password-stdin') && ! $this->input->isInteractive()) {
            $this->error('usa una terminal interactiva o suministra la contraseña por stdin.');

            return self::FAILURE;
        }
        $password = $this->option('password-stdin')
            ? rtrim((string) stream_get_contents(STDIN), "\r\n")
            : $this->secret('contraseña del propietario (mínimo 12 caracteres)');
        $data = [
            'email' => mb_strtolower(trim($this->argument('email'))),
            'business' => $this->option('business'),
            'branch' => $this->option('branch'),
            'name' => $this->option('name'),
            'currency' => $this->option('currency'),
            'timezone' => $this->option('timezone'),
            'password' => $password,
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'business' => ['required', 'string', 'max:120'],
            'branch' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'in:NIO,USD'],
            'timezone' => ['required', 'timezone:all', 'max:64'],
            'password' => ['required', 'string', Password::min(12), new PasswordByteLimit],
        ]);
        if ($validator->fails()) {
            $this->error('datos inválidos: '.implode(', ', $validator->errors()->keys()).'. no se creó ningún acceso.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($data): void {
            $business = Business::create(['name' => $data['business'], 'currency' => $data['currency'], 'timezone' => $data['timezone']]);
            $branch = $business->branches()->create(['name' => $data['branch']]);
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            Membership::create(['business_id' => $business->id, 'branch_id' => $branch->id, 'user_id' => $user->id, 'role' => Role::Owner]);
        });
        $this->info('negocio, sucursal y acceso del propietario creados.');

        return self::SUCCESS;
    }
}

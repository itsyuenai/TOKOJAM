<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email} {--name=}';

    protected $description = 'Create an administrator account or grant administrator access to an existing user.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Alamat email tidak valid.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $name = trim((string) ($this->option('name') ?: $this->ask('Nama admin')));
            if ($name === '') {
                $this->error('Nama admin wajib diisi.');

                return self::FAILURE;
            }

            $password = $this->secret('Password admin (minimal 12 karakter)');
            $confirmation = $this->secret('Ulangi password admin');

            if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
                $this->error('Password harus cocok dan minimal 12 karakter.');

                return self::FAILURE;
            }

            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Akses admin aktif untuk {$email}.");

        return self::SUCCESS;
    }
}

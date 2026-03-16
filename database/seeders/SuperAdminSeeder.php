<?php

namespace Database\Seeders;

use App\Models\AuthIdentity;
use App\Models\Utente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('SUPERADMIN_EMAIL or SUPERADMIN_PASSWORD is missing. SuperAdminSeeder skipped.');

            return;
        }

        $email = mb_strtolower(trim($email));

        $user = Utente::firstOrCreate(
            ['email' => $email],
            [
                'nome' => env('SUPERADMIN_FIRST_NAME', 'Super'),
                'cognome' => env('SUPERADMIN_LAST_NAME', 'Admin'),
                'password' => Hash::make($password),
                'stato' => 1,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if ((int) $user->stato !== 1) {
            $user->stato = 1;
        }

        if (! $user->password) {
            $user->password = Hash::make($password);
        }

        $user->save();

        AuthIdentity::firstOrCreate(
            [
                'provider' => 'email',
                'provider_user_id' => $email,
            ],
            [
                'utente_id' => $user->id,
                'provider_email' => $email,
            ]
        );

        $user->assignRole('superadmin');
    }
}

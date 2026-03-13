<?php

namespace Database\Seeders;

use App\Models\Utente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UtenteSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ADMIN

        $admin = Utente::create([
            'name' => 'Admin',
            'email' => 'admin@test.it',
            'password' => Hash::make('password')
        ]);

        $admin->assignRole('amministratore');


        // COLLABORATORI

        for ($i = 1; $i <= 2; $i++) {

            $user = Utente::create([
                'name' => 'Collaboratore ' . $i,
                'email' => 'collaboratore' . $i . '@test.it',
                'password' => Hash::make('password')
            ]);

            $user->assignRole('collaboratore');
        }


        // CLIENTI

        for ($i = 1; $i <= 10; $i++) {

            $user = Utente::create([
                'name' => 'Cliente ' . $i,
                'email' => 'cliente' . $i . '@test.it',
                'password' => Hash::make('password')
            ]);

            $user->assignRole('cliente');
        }
    }
}

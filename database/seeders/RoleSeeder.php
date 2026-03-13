<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amministratore = Role::firstOrCreate(['name' => 'amministratore', 'guard_name' => 'api']);
        $collaboratore  = Role::firstOrCreate(['name' => 'collaboratore',  'guard_name' => 'api']);
        $cliente        = Role::firstOrCreate(['name' => 'cliente',        'guard_name' => 'api']);

        $amministratore->syncPermissions(Permission::all());

        $collaboratore->syncPermissions([
            'dashboard.accesso',
            'ordini.gestisci',
            'anagrafiche.gestisci',
            'ticket.gestisci',
            'messaggi.gestisci',
        ]);

        $cliente->syncPermissions([]);
    }
}

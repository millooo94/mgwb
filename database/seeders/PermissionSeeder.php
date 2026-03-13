<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = [
            'dashboard.accesso',
            'admin.gestisci',
            'ordini.gestisci',
            'pagamenti.gestisci',
            'impostazioni.gestisci',
            'servizi_aggiuntivi.gestisci',
            'spedizioni.gestisci',
            'buoni_sconto.gestisci',
            'faq.gestisci',
            'ticket.gestisci',
            'anagrafiche.gestisci',
            'media.gestisci',
            'testi.gestisci',
            'messaggi.gestisci',
            'log.gestisci',
            'testimonials.gestisci',
            'banner.gestisci',
        ];

        foreach ($permissions as $name) {

            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'api',
            ]);
        }
    }
}

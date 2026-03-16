<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'backoffice.access',
            'users.manage',
            'orders.manage',
            'payments.manage',
            'settings.manage',
            'add_on_services.manage',
            'shipments.manage',
            'coupons.manage',
            'faqs.manage',
            'tickets.manage',
            'customers.manage',
            'media.manage',
            'content.manage',
            'messages.manage',
            'logs.manage',
            'testimonials.manage',
            'banners.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'api',
            ]);
        }
    }
}

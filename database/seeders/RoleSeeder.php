<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'api']);
        $customer = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'api']);

        $allPermissions = Permission::all();

        $superadmin->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);

        $staff->syncPermissions([
            'backoffice.access',
            'orders.manage',
            'customers.manage',
            'tickets.manage',
            'messages.manage',
        ]);

        $customer->syncPermissions([]);
    }
}

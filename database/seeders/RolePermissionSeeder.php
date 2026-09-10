<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Role::create([
            'name' => 'customer',
        ]);

        $agent = Role::create([
            'name' => 'agent',
        ]);

        $admin = Role::create([
            'name' => 'admin',
        ]);

        $createTicket = Permission::create([
            'name' => 'create-ticket',
        ]);

        $customer->permissions()->attach($createTicket);
    }
}

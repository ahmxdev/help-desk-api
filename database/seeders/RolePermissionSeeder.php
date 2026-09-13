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
        $customerRole = Role::create([
            'name' => 'customer',
        ]);

        $agentRole = Role::create([
            'name' => 'agent',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        $createTicket = Permission::create([
            'name' => 'create-ticket',
        ]);

        $assignAgent = Permission::create([
            'name' => 'assign-agent',
        ]);

        $customerRole->permissions()->attach($createTicket);
        $adminRole->permissions()->attach($assignAgent);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // With example data
        $customer = User::factory()->customer()->create([
            'password' => '12345678',
            'email' => 'customer@help-desk.com'
        ]);
        $agent = User::factory()->agent()->create([
            'password' => '12345678',
            'email' => 'agent@help-desk.com'
        ]);
        $admin = User::factory()->admin()->create([
            'password' => '12345678',
            'email' => 'admin@help-desk.com'
        ]);
    }
}

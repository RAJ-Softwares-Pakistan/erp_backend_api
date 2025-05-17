<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create super admin user
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => config('roles.roles.super_admin')
        ]);

        // Create organization owner
        User::factory()->create([
            'name' => 'Organization Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => config('roles.roles.org_owner')
        ]);

        // Create regular organization user
        User::factory()->create([
            'name' => 'Organization User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => config('roles.roles.org_user')
        ]);

        // Create additional random organization users
        User::factory(10)->create(['role' => config('roles.roles.org_user')]);
    }
}

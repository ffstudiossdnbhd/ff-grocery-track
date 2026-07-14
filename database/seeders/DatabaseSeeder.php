<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear Spatie permissions cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Stocker']);
        Role::firstOrCreate(['name' => 'Tracker']);

        // Create default Superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@ffgrocerytrack.com'],
            [
                'name' => 'Superadmin FFGrocery',
                'password' => Hash::make('password'),
            ]
        );

        // Assign superadmin role
        $superadmin->assignRole($superAdminRole);
    }
}

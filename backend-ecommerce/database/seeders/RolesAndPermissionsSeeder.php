<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * RolesAndPermissionsSeeder
 *
 * Creates the application roles (Admin, Customer) and assigns the
 * Admin role to the default test user created in DatabaseSeeder.
 *
 * Usage:
 *   php artisan db:seed --class=RolesAndPermissionsSeeder
 *   (or via DatabaseSeeder)
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. Reset cached roles & permissions so stale cache doesn't
        //    interfere when re-running the seeder during development.
        // ----------------------------------------------------------------
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ----------------------------------------------------------------
        // 2. Create roles (guard_name defaults to 'web'; we use 'api'
        //    because requests are authenticated via Passport tokens).
        // ----------------------------------------------------------------
        $adminRole    = Role::firstOrCreate(['name' => 'Admin',    'guard_name' => 'api']);
        $customerRole = Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'api']);

        $this->command->info("✅  Roles created: Admin, Customer");

        // ----------------------------------------------------------------
        // 3. Find (or create) the default admin user and assign the
        //    Admin role.
        // ----------------------------------------------------------------
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->assignRole($adminRole);

        $this->command->info("✅  Admin role assigned to: {$adminUser->email}");

        // ----------------------------------------------------------------
        // 4. Assign the Customer role to the default test user that
        //    DatabaseSeeder creates (test@example.com).
        // ----------------------------------------------------------------
        $testUser = User::where('email', 'test@example.com')->first();

        if ($testUser) {
            $testUser->assignRole($customerRole);
            $this->command->info("✅  Customer role assigned to: {$testUser->email}");
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use App\Models\Users\{UserRole, UserPermission};

class data_RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Providers
        UserPermission::firstOrCreate(['name' => 'providers_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'providers_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'providers_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'providers_mgmt_delete']);

        // Products
        UserPermission::firstOrCreate(['name' => 'products_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'products_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'products_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'products_mgmt_delete']);

        // Clients
        UserPermission::firstOrCreate(['name' => 'clients_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'clients_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'clients_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'clients_mgmt_delete']);

        // Clients Policies
        UserPermission::firstOrCreate(['name' => 'clients_policies_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'clients_policies_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'clients_policies_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'clients_policies_mgmt_delete']);

        // Users
        UserPermission::firstOrCreate(['name' => 'users_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'users_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'users_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'users_mgmt_delete']);

        // associates
        UserPermission::firstOrCreate(['name' => 'associates_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'associates_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'associates_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'associates_mgmt_delete']);

        // Submissions
        UserPermission::firstOrCreate(['name' => 'submissions_mgmt_view']);
        UserPermission::firstOrCreate(['name' => 'submissions_mgmt_create']);
        UserPermission::firstOrCreate(['name' => 'submissions_mgmt_update']);
        UserPermission::firstOrCreate(['name' => 'submissions_mgmt_delete']);

        // Associates
        UserPermission::firstOrCreate(['name' => 'associate_dashboard']);
        UserPermission::firstOrCreate(['name' => 'associate_submissions_view']);
        UserPermission::firstOrCreate(['name' => 'associate_submissions_create']);
        UserPermission::firstOrCreate(['name' => 'associate_submissions_update']);
        UserPermission::firstOrCreate(['name' => 'associate_submissions_delete']);
        UserPermission::firstOrCreate(['name' => 'associate_policies_view']);
        UserPermission::firstOrCreate(['name' => 'associate_clients_view']);
        UserPermission::firstOrCreate(['name' => 'associate_clients_create']);
        UserPermission::firstOrCreate(['name' => 'associate_clients_update']);
        UserPermission::firstOrCreate(['name' => 'associate_clients_delete']);
        UserPermission::firstOrCreate(['name' => 'associate_teams_view']);
        UserPermission::firstOrCreate(['name' => 'payroll_self_view']);

        // create roles and assign created permissions
        $role_associate = UserRole::firstOrCreate(['name' => 'sales-associate']);
        $role_associate->givePermissionTo([
            // Dashboard
            'associate_dashboard',
            // Submissions
            'associate_submissions_view',
            'associate_submissions_create',
            'associate_submissions_update',
            'associate_submissions_delete',
            // Policies
            'associate_policies_view',
            // Clients
            'associate_clients_view',
            'associate_clients_create',
            'associate_clients_update',
            'associate_clients_delete',
            // Payroll
            'payroll_self_view',
        ]);

        $role_manager = UserRole::firstOrCreate(['name' => 'sales-manager']);
        $role_manager->givePermissionTo(['associate_teams_view']);

        $role_hqstaff = UserRole::firstOrCreate(['name' => 'hq-staff']);
        $role_hqstaff->givePermissionTo([
            // Providers
            'providers_mgmt_view',
            'providers_mgmt_create',
            'providers_mgmt_update',
            // Products
            'products_mgmt_view',
            'products_mgmt_create',
            'products_mgmt_update',
            // Clients
            'clients_mgmt_view',
            'clients_mgmt_create',
            'clients_mgmt_update',
            // Clients
            'clients_policies_mgmt_view',
            'clients_policies_mgmt_create',
            'clients_policies_mgmt_update',
            // Users
            'users_mgmt_view',
            'users_mgmt_create',
            'users_mgmt_update',
            // associates
            'associates_mgmt_view',
            'associates_mgmt_create',
            'associates_mgmt_update',
            // Submissions
            'submissions_mgmt_view',
            'submissions_mgmt_create',
            'submissions_mgmt_update',
            'submissions_mgmt_delete',
        ]);

        $role_it = UserRole::firstOrCreate(['name' => 'it-department']);
        $role_it->givePermissionTo([
            // Providers
            'providers_mgmt_view',
            'providers_mgmt_create',
            'providers_mgmt_update',
            // Products
            'products_mgmt_view',
            'products_mgmt_create',
            'products_mgmt_update',
            // Clients
            'clients_mgmt_view',
            'clients_mgmt_create',
            'clients_mgmt_update',
            // Clients
            'clients_policies_mgmt_view',
            'clients_policies_mgmt_create',
            'clients_policies_mgmt_update',
            // Users
            'users_mgmt_view',
            'users_mgmt_create',
            'users_mgmt_update',
            // associates
            'associates_mgmt_view',
            'associates_mgmt_create',
            'associates_mgmt_update',
            // Submissions
            'submissions_mgmt_view',
            'submissions_mgmt_create',
            'submissions_mgmt_update',
        ]);

        $role_superadmin = UserRole::firstOrCreate(['name' => 'super-admin']);
    }
}

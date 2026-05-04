<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tours.view', 'tours.create', 'tours.edit', 'tours.delete', 'tours.confirm', 'tours.assign_staff',
            'hotels.view', 'hotels.create', 'hotels.edit', 'hotels.manage_bookings', 'hotels.change_status',
            'transports.view', 'transports.create', 'transports.edit', 'transports.assign',
            'visas.view', 'visas.create', 'visas.edit', 'visas.process',
            'counterparties.view', 'counterparties.create', 'counterparties.edit', 'counterparties.delete',
            'reports.view_financial', 'reports.view_operational', 'reports.export',
            'offers.view', 'offers.create', 'offers.edit', 'offers.accept', 'offers.reject',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
            'documents.view', 'documents.upload', 'documents.delete',
            'pdf_export.generate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $rolePermissions = [
            'super_admin' => $permissions,
            'manager' => [
                'tours.view', 'tours.create', 'tours.edit', 'tours.confirm', 'tours.assign_staff',
                'hotels.view', 'hotels.create', 'hotels.edit', 'hotels.manage_bookings', 'hotels.change_status',
                'transports.view', 'transports.create', 'transports.edit', 'transports.assign',
                'visas.view', 'visas.create', 'visas.edit',
                'counterparties.view', 'counterparties.create', 'counterparties.edit',
                'reports.view_operational', 'reports.export',
                'offers.view', 'offers.create', 'offers.edit', 'offers.accept', 'offers.reject',
                'documents.view', 'documents.upload',
                'pdf_export.generate',
            ],
            'sales' => [
                'tours.view', 'tours.create', 'tours.edit',
                'offers.view', 'offers.create', 'offers.edit',
                'counterparties.view',
                'documents.view',
            ],
            'accountant' => [
                'tours.view',
                'reports.view_financial', 'reports.view_operational', 'reports.export',
                'counterparties.view',
                'documents.view',
            ],
            'visa_officer' => [
                'tours.view',
                'visas.view', 'visas.create', 'visas.edit', 'visas.process',
                'documents.view', 'documents.upload',
            ],
            'transport_manager' => [
                'tours.view',
                'transports.view', 'transports.create', 'transports.edit', 'transports.assign',
                'documents.view',
            ],
            'hotel_khiva' => [
                'tours.view',
                'hotels.view', 'hotels.manage_bookings',
            ],
            'hotel_samarkand' => [
                'tours.view',
                'hotels.view', 'hotels.manage_bookings',
            ],
            'hotel_bukhara' => [
                'tours.view',
                'hotels.view', 'hotels.manage_bookings',
            ],
            'counterparty_hotel' => ['hotels.view', 'hotels.manage_bookings'],
            'counterparty_restaurant' => ['tours.view'],
            'counterparty_transport' => ['tours.view', 'transports.view'],
            'counterparty_guide' => ['tours.view'],
            'counterparty_tour' => ['tours.view', 'offers.view', 'offers.create'],
            'staff' => ['tours.view', 'hotels.view', 'documents.view'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }
    }
}

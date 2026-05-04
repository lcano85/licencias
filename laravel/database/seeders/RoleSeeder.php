<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder {

    public function run(): void {
        $masterAdmin = Role::firstOrCreate(['name' => 'Master Admin']);
        $masterAdmin->givePermissionTo([
            'create-role',
            'edit-role',
            'delete-role',
            'create-user',
            'edit-user',
            'delete-user'
        ]);

        $contadorPermissions = [
            'list-budget',
            'edit-budget',
            'listing-billing-list',
            'edit-billing-list',
            'view-budget',
            'view-billing-list',
            'view-validation',
            'view-credit-notes',
        ];

        $gerenciaPermissions = [
            'list-budget',
            'listing-billing-list',
            'view-budget',
            'view-billing-list',
            'view-validation',
            'view-credit-notes',
            'approve-validation',
        ];

        $contador = Role::firstOrCreate(['name' => 'Contador']);
        $contador->syncPermissions(Permission::whereIn('name', $contadorPermissions)->get());

        $gerencia = Role::firstOrCreate(['name' => 'Gerencia']);
        $gerencia->syncPermissions(Permission::whereIn('name', $gerenciaPermissions)->get());
    }
}

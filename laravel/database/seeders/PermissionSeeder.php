<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder {

    public function run(): void {
        $permissions = [
            'list-role',
            'create-role',
            'edit-role',
            'delete-role',
            'list-user',
            'create-user',
            'edit-user',
            'delete-user',
            'list-budget',
            'create-budget',
            'edit-budget',
            'delete-budget',
            'listing-billing-list',
            'create-billing-list',
            'edit-billing-list',
            'delete-billing-list',
            'view-budget',
            'view-billing-list',
            'view-validation',
            'view-credit-notes',
            'approve-validation',
        ];
 
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

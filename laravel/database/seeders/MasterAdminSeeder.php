<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MasterAdminSeeder extends Seeder {

    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'master-admin@gmail.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('123456789')
            ]
        );
        $superAdmin->assignRole('Master Admin');
    }
}

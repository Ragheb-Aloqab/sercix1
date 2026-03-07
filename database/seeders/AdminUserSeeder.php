<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@servvmotors.test';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'phone' => '+966563223968',
                'password' => 'password',
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );
    }
}

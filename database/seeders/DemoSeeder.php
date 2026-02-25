<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@servx.test'],
            [
                'name' => 'Admin',
                'phone' => '+966563223961',
                'password' => 'password',
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}

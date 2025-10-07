<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'), // simple password for testing
            ]
        );

        $this->command->info('Test admin user created: admin@test.com / password');
    }
}

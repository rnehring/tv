<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'rnehring'],
            [
                'name' => 'Ryan Nehring',
                'email' => 'rnehring@andronaco.com',
                'password' => 'abc123', // hashed automatically by the model cast
                'is_admin' => true,
            ]
        );
    }
}

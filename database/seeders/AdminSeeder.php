<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin role if not exists
       
        $AdminRole = Role::firstOrCreate(['name' => 'admin']);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'), // Change to a secure password
        ]);

        // Assign role to the user
        $user->assignRole($AdminRole);

        echo "Super Admin created successfully! \n";
    }
}

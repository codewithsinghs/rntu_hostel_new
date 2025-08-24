<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin role if not exists
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $AdminRole = Role::firstOrCreate(['name' => 'admin']);

        $university = University::create([
            'name' => 'RNTU',
            'location' => 'Bhopal',
            'state' => 'MP',
            'district' => 'Bhopal',
            'pincode' => '462003',
            'address' => 'Bhopal',
            'mobile' => '9874563210',
            'email' => 'hostel@rntu.ac.in',
        ]);
        // Create a new Super Admin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('12345678'), // Change to a secure password
            'university_id' => $university->id,
        ]);

        // Assign role to the user
        $user->assignRole($superAdminRole);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'), // Change to a secure password
            'university_id' => $university->id,
        ]);

        // Assign role to the user
        $user->assignRole($AdminRole);

        echo "Super Admin created successfully! \n";
    }
}

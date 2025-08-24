<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resident']);
        Role::create(['name' => 'warden']);
        Role::create(['name' => 'security']);
        Role::create(['name' => 'mess_manager']);
        Role::create(['name' => 'gym_manager']);
        Role::create(['name' => 'hod']);
        Role::create(['name' => 'accountant']);

        $this->call([
            SuperAdminSeeder::class,
            FacultyCourse::class,
            Fees::class,
            AccessorySeeder::class,
            HostelSeeder::class
        ]);
    }
}

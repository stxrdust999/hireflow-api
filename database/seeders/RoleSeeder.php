<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Admin', 'slug' => 'admin']);
        Role::create(['name' => 'Recruiter', 'slug' => 'recruiter']);
        Role::create(['name' => 'Hiring Manager', 'slug' => 'hiring-manager']);
        Role::create(['name' => 'Candidate', 'slug' => 'candidate']);
    }
}

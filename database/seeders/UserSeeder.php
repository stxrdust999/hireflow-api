<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => config('services.admin.name'),
            'email' => config('services.admin.email'),
            'email_verified_at' => now(),
            'password' => Hash::make(config('services.admin.password')),
            'remember_token' => null
        ]);

        $admin->roles()->attach(Role::where('slug', 'admin')->first()->id);

        $recruiterRole = Role::where('slug', 'recruiter')->first()->id;
        $hiringManagerRole = Role::where('slug', 'hiring-manager')->first()->id;
        $candidateRole = Role::where('slug', 'candidate')->first()->id;


        User::factory()->count(5)->create()->each(fn($u) => $u->roles()->attach($recruiterRole));
        User::factory()->count(5)->create()->each(fn($u) => $u->roles()->attach($hiringManagerRole));
        User::factory()->count(20)->create()->each(fn($u) => $u->roles()->attach($candidateRole));
    }
}

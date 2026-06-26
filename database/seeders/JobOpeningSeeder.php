<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobOpening;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobOpeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recruiters = User::whereHas('roles', fn($q) => $q->where('slug', 'recruiter'))->get();

        if ($recruiters->isEmpty() || Company::count() === 0)
            return;

        JobOpening::factory()->count(20)->create([
            'company_id' => fn() => Company::inRandomOrder()->first()->id,
            'created_by' => fn() => $recruiters->random()->id,
        ]);
    }
}

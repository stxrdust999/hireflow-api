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
        $hiringManagers = User::whereHas('roles', fn($q) => $q->where('slug', 'hiring-manager'))->get();

        if ($recruiters->isEmpty() || $hiringManagers->isEmpty() || Company::count() === 0)
            return;

        JobOpening::factory()->count(20)->create([
            'company_id' => fn() => Company::inRandomOrder()->first()->id,
            'created_by' => fn() => $recruiters->random()->id,
        ])->each(function (JobOpening $jobOpening) use ($hiringManagers) {
            $jobOpening->hiringManagers()->attach(
                $hiringManagers->random(min(2, $hiringManagers->count()))->pluck('id')
            );
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $candidates = User::whereHas('roles', fn($q) => $q->where('slug', 'candidate'))->get();
        $jobs = JobOpening::with('stages')->get();

        if ($candidates->isEmpty() || $jobs->isEmpty())
            return;

        for ($i = 0; $i < 40; $i++) {
            $job = $jobs->random();
            $stage = $job->stages->random();

            Application::factory()->create([
                'job_id' => $job->id,
                'current_stage_id' => $stage->id,
                'candidate_id' => $candidates->random()->id,
            ]);
        }
    }
}

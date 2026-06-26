<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationStageLog;
use App\Models\JobStage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationStageLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recruiters = User::whereHas('roles', fn($q) => $q->whereIn('slug', ['recruiter', 'hiring-manager']))->get();
        $applications = Application::with('job.stages')->get();

        if ($recruiters->isEmpty() || $applications->isEmpty())
            return;

        for ($i = 0; $i < 60; $i++) {
            $app = $applications->random();

            if ($app->job->stages->isEmpty())
                continue;

            ApplicationStageLog::factory()->create([
                'application_id' => $app->id,
                'stage_id' => $app->job->stages->random()->id,
                'moved_by' => $recruiters->random()->id,
            ]);
        }
    }
}

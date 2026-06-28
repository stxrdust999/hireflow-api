<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationStageLog;
use App\Models\JobStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationStageLog>
 */
class ApplicationStageLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'stage_id' => function (array $attributes) {
                $application = Application::find($attributes['application_id'])
                    ?? Application::factory()->create();

                return JobStage::factory()->create([
                    'job_id' => $application->job_id,
                ])->id;
            },
            'moved_by' => User::factory(),
            'moved_at' => fake()->dateTimeBetween('-6 months', 'now')
        ];
    }
}

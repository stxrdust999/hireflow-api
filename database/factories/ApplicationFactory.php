<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobOpening;
use App\Models\JobStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => JobOpening::factory(),
            'current_stage_id' => function (array $attributes) {
                return JobStage::factory()->create([
                    'job_id' => $attributes['job_id'] ?? JobOpening::factory(),
                ])->id;
            },
            'candidate_id' => User::factory(),
            'resume_url' => fake()->url()
        ];
    }
}

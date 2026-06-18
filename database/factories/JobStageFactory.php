<?php

namespace Database\Factories;

use App\Models\JobOpening;
use App\Models\JobStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobStage>
 */
class JobStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = ['screening', 'hr-interview', 'technical-interview', 'offer', 'hired'];

        return [
            'job_id' => JobOpening::factory(),
            'name' => fake()->randomElement($names),
            'order' => fake()->numberBetween(1, 5)
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobOpening>
 */
class JobOpeningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['full-time', 'part-time', 'contract', 'internship'];
        $statuses = ['draft', 'published', 'closed'];

        return [
            'company_id' => Company::factory(),
            'created_by' => User::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->text(),
            'location' => fake()->locale(),
            'type' => fake()->randomElement($types),
            'status' => fake()->randomElement($statuses)
        ];
    }
}

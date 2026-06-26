<?php

namespace Database\Seeders;

use App\Models\JobOpening;
use App\Models\JobStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = ['screening', 'hr-interview', 'technical-interview', 'offer', 'hired'];

        JobOpening::all()->each(function ($job) use ($stages) {
            foreach ($stages as $index => $name) {
                JobStage::create([
                    'job_id' => $job->id,
                    'name' => $name,
                    'order' => $index + 1
                ]);
            }
        });
    }
}

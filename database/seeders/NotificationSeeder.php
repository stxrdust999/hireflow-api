<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobOpening;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $applications = Application::with(['job.stages', 'currentStage', 'candidate'])->get();
        $jobs = JobOpening::all();

        if ($users->isEmpty() || $jobs->isEmpty())
            return;

        $types = [
            'application_received', 'application_advanced', 'application_rejected',
            'application_proposal', 'application_hired', 'application_withdrawn',
            'application_closed', 'interview_scheduled', 'job_deadline_near',
            'stage_pending_review'
        ];

        for ($i = 0; $i < 80; $i++) {
            $type = fake()->randomElement($types);

            Notification::create([
                'user_id' => $users->random()->id,
                'type' => $type,
                'data' => $this->generateNotificationData($type, $applications, $jobs),
                'read_at' => fake()->optional(0.4)->dateTimeBetween('-1 month', 'now'),
            ]);
        }
    }

    private function generateNotificationData(string $type, $applications, $jobs): array
    {
        // Define quais notificações dependem diretamente de uma candidatura ativa
        $needsApplication = in_array($type, [
            'application_advanced',
            'application_rejected',
            'application_proposal',
            'application_hired',
            'application_withdrawn',
            'application_closed',
            'interview_scheduled'
        ]);

        $application = ($needsApplication && $applications->isNotEmpty()) ? $applications->random() : null;
        $job = $application ? $application->job : ($jobs->isNotEmpty() ? $jobs->random() : null);

        if (!$job)
            return [];

        return match ($type) {
            'application_received' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
            ],
            'application_advanced' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'stage_name' => $application?->currentStage?->name ?? 'Triagem',
                'application_id' => $application?->id,
            ],
            'application_rejected',
            'application_proposal',
            'application_hired',
            'application_closed' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'application_id' => $application?->id,
            ],
            'application_withdrawn' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'candidate_name' => $application?->candidate?->name ?? fake()->name(),
                'application_id' => $application?->id,
            ],
            'interview_scheduled' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'application_id' => $application?->id,
                'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d H:i:s'),
            ],
            'job_deadline_near' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'closes_at' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            ],
            'stage_pending_review' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'stage_name' => fake()->randomElement(['Triagem', 'Entrevista RH', 'Entrevista Técnica']),
                'pending_count' => fake()->numberBetween(1, 10),
            ],
            default => [
                'job_id' => $job->id,
                'job_title' => $job->title,
            ],
        };
    }
}

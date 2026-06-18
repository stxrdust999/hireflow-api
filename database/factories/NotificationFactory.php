<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobOpening;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'application_received',
            'application_advanced',
            'application_rejected',
            'application_proposal',
            'application_hired',
            'application_withdrawn',
            'application_closed',
            'interview_scheduled',
            'job_deadline_near',
            'stage_pending_review'
        ]);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'data' => fn (array $attributes) => $this->generateNotificationData($attributes['type']),
            'read_at' => fake()->optional(0.4)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Gera os dados flexíveis do payload JSON dependendo do tipo da notificação.
     */
    private function generateNotificationData(string $type): array
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

        $application = $needsApplication ? Application::factory()->create() : null;
        $job = $application ? $application->job : JobOpening::factory()->create();

        return match ($type) {
            'application_received' => [
                'job_id'    => $job->id,
                'job_title' => $job->title,
            ],
            'application_advanced' => [
                'job_id'         => $job->id,
                'job_title'      => $job->title,
                'stage_name'     => $application->currentStage?->name ?? 'Triagem',
                'application_id' => $application->id,
            ],
            'application_rejected',
            'application_proposal',
            'application_hired',
            'application_closed' => [
                'job_id'         => $job->id,
                'job_title'      => $job->title,
                'application_id' => $application->id,
            ],
            'application_withdrawn' => [
                'job_id'         => $job->id,
                'job_title'      => $job->title,
                'candidate_name' => $application->candidate?->name ?? fake()->name(),
                'application_id' => $application->id,
            ],
            'interview_scheduled' => [
                'job_id'         => $job->id,
                'job_title'      => $job->title,
                'application_id' => $application->id,
                'scheduled_at'   => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d H:i:s'),
            ],
            'job_deadline_near' => [
                'job_id'    => $job->id,
                'job_title' => $job->title,
                'closes_at' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            ],
            'stage_pending_review' => [
                'job_id'        => $job->id,
                'job_title'     => $job->title,
                'stage_name'    => fake()->randomElement(['Triagem', 'Entrevista RH', 'Entrevista Técnica']),
                'pending_count' => fake()->numberBetween(1, 10),
            ],
            default => [
                'job_id'    => $job->id,
                'job_title' => $job->title,
            ],
        };
    }
}

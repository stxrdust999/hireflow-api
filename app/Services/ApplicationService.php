<?php

namespace App\Services;

use App\Enums\ApplicationEnum;
use App\Models\Application;
use App\Models\ApplicationStageLog;
use App\Models\JobStage;
use App\Models\User;

use function Illuminate\Support\now;

class ApplicationService
{
    /**
     * Registra a candidatura de um candidato a uma vaga.
     * A candidatura é criada na primeira etapa do pipeline da vaga (menor 'order').
     * O status inicial é 'pending'.
     *
     * @param  array{
     *     job_id: string,
     *     candidate_id: int,
     *     resume_url: string
     * } $data
     * @return Application
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Vaga sem stages cadastradas
     */
    public function apply(array $data): Application
    {
        $firstStage = JobStage::where('job_id', $data['job_id'])
            ->orderBy('order')
            ->firstOrFail();

        $application = Application::create([
            'job_id' => $data['job_id'],
            'current_stage_id' => $firstStage->id,
            'candidate_id' => $data['candidate_id'],
            'resume_url' => $data['resume_url'],
            'status' => ApplicationEnum::Pending
        ]);

        return $application;
    }

    /**
     * Move uma candidatura para uma nova etapa do pipeline.
     * Registra a movimentação em ApplicationStageLog para auditoria.
     * Se a etapa for a última do pipeline, o status é atualizado para 'approved'.
     *
     * @param  Application $application Candidatura resolvida via route model binding
     * @param  JobStage    $stage       Etapa de destino resolvida via route model binding
     * @param  User        $author      Usuário que realizou a movimentação (recruiter ou HM)
     * @return Application
     */
    public function move(Application $application, JobStage $stage, User $author): Application
    {
        ApplicationStageLog::create([
            'application_id' => $application->id,
            'stage_id'       => $stage->id,
            'moved_by'       => $author->id,
            'moved_at'       => now(),
        ]);

        $isLastStage = !JobStage::where('job_id', $application->job_id)
            ->where('order', '>', $stage->order)
            ->exists();

        $application->update([
            'current_stage_id' => $stage->id,
            'status' => $isLastStage ? ApplicationEnum::Approved : ApplicationEnum::InProgress,
        ]);

        return $application;
    }

    /**
     * Retira uma candidatura do pipeline.
     * O registro não é deletado — apenas o status é marcado como 'withdrawn'
     * para preservar o histórico de auditoria em ApplicationStageLog.
     *
     * @param  Application $application Candidatura resolvida via route model binding
     * @return void
     */
    public function withdraw(Application $application): void
    {
        $application->update([
            'status' => ApplicationEnum::Withdrawn
        ]);
    }
}

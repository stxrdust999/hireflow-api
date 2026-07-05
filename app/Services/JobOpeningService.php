<?php

namespace App\Services;

use App\Enums\JobOpeningEnum;
use App\Models\JobOpening;

class JobOpeningService
{
    /**
     * Cria uma nova vaga de emprego com as etapas padrão do pipeline.
     * Toda vaga criada recebe automaticamente 5 etapas: Triagem → Entrevista RH
     * → Entrevista Técnica → Proposta → Contratado.
     *
     * @param  array{
     *     company_id: string,
     *     created_by: int,
     *     title: string,
     *     description: string,
     *     location: string,
     *     type: string
     * } $data
     * @return JobOpening
     */
    public function create(array $data): JobOpening
    {
        $jobOpening = JobOpening::create([
            'company_id'  => $data['company_id'],
            'created_by'  => $data['created_by'],
            'title'       => $data['title'],
            'description' => $data['description'],
            'location'    => $data['location'],
            'type'        => $data['type'],
        ]);

        $jobOpening->stages()->createMany([
            ['name' => 'Triagem',            'order' => 1],
            ['name' => 'Entrevista RH',      'order' => 2],
            ['name' => 'Entrevista Técnica', 'order' => 3],
            ['name' => 'Proposta',           'order' => 4],
            ['name' => 'Contratado',         'order' => 5],
        ]);

        return $jobOpening;
    }

    /**
     * Atualiza os dados de uma vaga existente.
     * Campos não presentes no $fillable do model são ignorados automaticamente.
     *
     * @param  array<string, mixed> $data Campos a atualizar
     * @param  JobOpening $jobOpening     Vaga resolvida via route model binding
     * @return JobOpening
     */
    public function update(array $data, JobOpening $jobOpening): JobOpening
    {
        $jobOpening->update($data);

        return $jobOpening;
    }

    /**
     * Publica uma vaga, tornando-a visível no portal público.
     * Só é possível publicar vagas com status 'draft'.
     *
     * @param  JobOpening $jobOpening Vaga resolvida via route model binding
     * @return JobOpening
     *
     * @throws \Exception Vaga não está em estado 'draft'
     */
    public function publish(JobOpening $jobOpening): JobOpening
    {
        if ($jobOpening->status !== JobOpeningEnum::Draft) {
            throw new \Exception('Não foi possível publicar essa vaga.');
        }

        $jobOpening->update(['status' => JobOpeningEnum::Published]);

        return $jobOpening;
    }

    /**
     * Encerra uma vaga, impedindo novas candidaturas.
     * Só é possível fechar vagas com status 'published'.
     *
     * @param  JobOpening $jobOpening Vaga resolvida via route model binding
     * @return JobOpening
     *
     * @throws \Exception Vaga não está em estado 'published'
     */
    public function close(JobOpening $jobOpening): JobOpening
    {
        if ($jobOpening->status !== JobOpeningEnum::Published) {
            throw new \Exception('Não foi possível fechar essa vaga.');
        }

        $jobOpening->update(['status' => JobOpeningEnum::Closed]);

        return $jobOpening;
    }
}

<?php

namespace App\Http\Controllers\Api\Jobs;

use App\Enums\JobOpeningEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobOpening\StoreJobOpeningRequest;
use App\Http\Requests\JobOpening\UpdateJobOpeningRequest;
use App\Http\Resources\JobOpeningResource;
use App\Models\JobOpening;
use App\Services\JobOpeningService;
use Illuminate\Http\JsonResponse;

class JobOpeningController extends Controller
{
    public function __construct(
        private readonly JobOpeningService $service
    ) {
    }

    /**
     * Lista as vagas publicadas, com company e stages carregadas (eager loading).
     * Rota pública — só expõe vagas em status 'published'.
     *
     * @return JsonResponse 200 com a coleção de vagas
     */
    public function index(): JsonResponse
    {
        $jobOpenings = JobOpening::with(['company', 'stages'])
            ->where('status', JobOpeningEnum::Published)
            ->get();

        return JobOpeningResource::collection($jobOpenings)->response()->setStatusCode(200);
    }

    /**
     * Retorna uma vaga específica, resolvida via route model binding.
     * Rota pública.
     *
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 200 com a vaga
     */
    public function show(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening->load(['company', 'stages']);

        return (new JobOpeningResource($jobOpening))->response()->setStatusCode(200);
    }

    /**
     * Cria uma nova vaga. A autoria (created_by) vem do usuário autenticado,
     * nunca do corpo da requisição — evita forjar autoria.
     * Rota protegida (admin, recruiter).
     *
     * @param  StoreJobOpeningRequest $request
     * @return JsonResponse 201 com a vaga criada
     */
    public function store(StoreJobOpeningRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $jobOpening = $this->service->create($data);

        return (new JobOpeningResource($jobOpening))->response()->setStatusCode(201);
    }

    /**
     * Atualiza os dados de uma vaga existente.
     * Rota protegida (admin, recruiter).
     *
     * @param  UpdateJobOpeningRequest $request
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 200 com a vaga atualizada
     */
    public function update(UpdateJobOpeningRequest $request, JobOpening $jobOpening): JsonResponse
    {
        $jobOpening = $this->service->update($request->validated(), $jobOpening);
        $jobOpening->load(['company', 'stages']);

        return (new JobOpeningResource($jobOpening))->response()->setStatusCode(200);
    }

    /**
     * Publica uma vaga (draft -> published). A validação de transição fica no Service.
     * Rota protegida (admin, recruiter).
     *
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 200 com a vaga publicada
     *
     * @throws \Exception Vaga não está em estado 'draft'
     */
    public function publish(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening = $this->service->publish($jobOpening);
        $jobOpening->load(['company', 'stages']);

        return (new JobOpeningResource($jobOpening))->response()->setStatusCode(200);
    }

    /**
     * Encerra uma vaga (published -> closed). A validação de transição fica no Service.
     * Rota protegida (admin, recruiter).
     *
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 200 com a vaga encerrada
     *
     * @throws \Exception Vaga não está em estado 'published'
     */
    public function close(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening = $this->service->close($jobOpening);
        $jobOpening->load(['company', 'stages']);

        return (new JobOpeningResource($jobOpening))->response()->setStatusCode(200);
    }

    /**
     * Remove uma vaga permanentemente.
     * Rota protegida (somente admin).
     *
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 204 sem corpo
     */
    public function destroy(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api\Applications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\MoveApplicationRequest;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\JobOpening;
use App\Models\JobStage;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $service)
    {
    }

    /**
     * Lista as candidaturas de uma vaga específica.
     * Rota protegida (admin, recruiter, hiring-manager) — a JobOpeningPolicy
     * garante que o hiring-manager só liste candidaturas das próprias vagas.
     *
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 200 com a coleção de candidaturas
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não puder ver as candidaturas da vaga
     */
    public function index(JobOpening $jobOpening): JsonResponse
    {
        $this->authorize('viewApplications', $jobOpening);

        $applications = Application::with(['job', 'candidate', 'currentStage'])
            ->where('job_id', $jobOpening->id)
            ->get();

        return ApplicationResource::collection($applications)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Retorna uma candidatura específica, resolvida via route model binding.
     * Rota protegida (admin, recruiter, hiring-manager) — a ApplicationPolicy
     * garante que o hiring-manager só veja candidaturas das próprias vagas.
     *
     * @param  Application $application Candidatura resolvida pelo UUID na rota
     * @return JsonResponse 200 com a candidatura
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não puder ver a candidatura
     */
    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $application->load(['job', 'candidate', 'currentStage']);

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Registra a candidatura do usuário autenticado a uma vaga.
     * Os dados vêm de três origens: resume_url do corpo, candidate_id do token
     * (nunca do corpo — evita forjar identidade) e job_id da URL (rota aninhada).
     * Rota protegida (candidate).
     *
     * @param  StoreApplicationRequest $request
     * @param  JobOpening $jobOpening Vaga resolvida pelo UUID na rota
     * @return JsonResponse 201 com a candidatura criada
     *
     * @throws \Exception Candidato já se candidatou a essa vaga
     */
    public function store(StoreApplicationRequest $request, JobOpening $jobOpening): JsonResponse
    {
        $application = $this->service->apply([
            'resume_url' => $request->validated('resume_url'),
            'candidate_id' => Auth::id(),
            'job_id' => $jobOpening->id
        ]);

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }


    /**
     * Move uma candidatura para outra etapa do pipeline.
     * A etapa de destino vem do corpo (stage_id) — não há avanço automático;
     * recruiter/HM escolhem livremente a etapa. O Service registra a
     * movimentação em ApplicationStageLog para auditoria.
     * Rota protegida (admin, recruiter, hiring-manager) + ApplicationPolicy::move.
     *
     * @param  MoveApplicationRequest $request
     * @param  Application $application Candidatura resolvida pelo UUID na rota
     * @return JsonResponse 200 com a candidatura atualizada
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não puder mover a candidatura
     */
    public function move(MoveApplicationRequest $request, Application $application): JsonResponse
    {
        $this->authorize('move', $application);

        $jobStage = JobStage::findOrFail($request->validated('stage_id'));

        $author = User::findOrFail(Auth::id());

        $application = $this->service->move(
            $application,
            $jobStage,
            $author
        );

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Retira uma candidatura do processo seletivo.
     * O registro não é deletado — apenas marcado como 'withdrawn' pelo Service.
     * Rota protegida (candidate) + ApplicationPolicy::withdraw, que garante
     * que apenas o próprio dono saque a candidatura.
     *
     * @param  Application $application Candidatura resolvida pelo UUID na rota
     * @return JsonResponse 204 sem corpo
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se a candidatura não for do usuário
     */
    public function withdraw(Application $application): JsonResponse
    {
        $this->authorize('withdraw', $application);

        $this->service->withdraw($application);

        return response()->json(null, 204);
    }

    /**
     * Lista as candidaturas do usuário autenticado.
     * Não usa Policy: por ser uma listagem das próprias candidaturas, a
     * segurança vem do escopo da query (where candidate_id = usuário logado) —
     * é impossível retornar candidatura de outro.
     * Rota protegida (candidate).
     *
     * @return JsonResponse 200 com a coleção de candidaturas do usuário
     */
    public function myApplications(): JsonResponse
    {
        $applications = Application::with(['job', 'candidate', 'currentStage'])
            ->where('candidate_id', Auth::id())
            ->get();

        return ApplicationResource::collection($applications)
            ->response()
            ->setStatusCode(200);
    }
}

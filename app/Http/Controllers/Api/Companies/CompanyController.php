<?php

namespace App\Http\Controllers\Api\Companies;

use App\Enums\JobOpeningEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $service
    ) {
    }

    /**
     * Lista as empresas ativas. Rota pública.
     * Não carrega jobOpenings — a listagem é enxuta (whenLoaded no Resource
     * omite o campo). A página de uma empresa (show) é quem traz as vagas.
     *
     * @return JsonResponse 200 com a coleção de empresas
     */
    public function index(): JsonResponse
    {
        $companies = Company::where('is_active', true)->get();

        return CompanyResource::collection($companies)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Retorna uma empresa específica, buscada pelo slug.
     * Só empresas ativas — desativada devolve 404 (não existe pro público).
     * Carrega apenas as vagas publicadas — rascunhos/fechadas não vazam pro público.
     * Rota pública.
     *
     * @param  string $slug Slug da empresa vindo da rota
     * @return JsonResponse 200 com a empresa e suas vagas publicadas
     */
    public function show(string $slug): JsonResponse
    {
        $company = Company::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $company->load(['jobOpenings' => fn($query) => $query->where('status', JobOpeningEnum::Published)]);

        return (new CompanyResource($company))->response()->setStatusCode(200);
    }

    /**
     * Cria uma nova empresa. O slug é gerado no Service a partir do name.
     * Rota protegida (somente admin).
     *
     * @param  StoreCompanyRequest $request
     * @return JsonResponse 201 com a empresa criada
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->service->create($request->validated());

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    /**
     * Atualiza uma empresa existente (edição parcial). O slug é congelado.
     * Rota protegida (somente admin).
     *
     * @param  UpdateCompanyRequest $request
     * @param  Company $company Empresa resolvida pelo UUID na rota
     * @return JsonResponse 200 com a empresa atualizada
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company = $this->service->update($request->validated(), $company);

        return (new CompanyResource($company))->response()->setStatusCode(200);
    }

    /**
     * Desativa uma empresa (is_active = false) sem deletar o registro.
     * Preserva vagas, candidaturas e logs de auditoria vinculados.
     * Rota protegida (somente admin).
     *
     * @param  Company $company Empresa resolvida pelo UUID na rota
     * @return JsonResponse 204 sem corpo
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->service->deactivate($company);

        return response()->json(null, 204);
    }
}

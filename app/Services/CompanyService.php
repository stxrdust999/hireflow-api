<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Str;

class CompanyService
{
    /**
     * Cria uma nova empresa.
     * O slug é gerado a partir do nome (nunca vem do cliente) e resolve
     * colisões com sufixo numérico (acme-corp, acme-corp-2, ...).
     *
     * @param  array{
     *     name: string,
     *     logo_url?: string,
     * } $data
     * @return Company
     */
    public function create(array $data): Company
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $company = Company::create($data);

        return $company;
    }

    /**
     * Atualiza uma empresa existente.
     * O slug é congelado após a criação — renomear a empresa não regera o
     * slug, para não quebrar links já compartilhados da página pública.
     *
     * @param  array{
     *     name?: string,
     *     logo_url?: string,
     * } $data Apenas as chaves que passaram na validação (edição parcial)
     * @param  Company $company Empresa resolvida via route model binding
     * @return Company
     */
    public function update(array $data, Company $company): Company
    {
        $company->update($data);

        return $company;
    }

    /**
     * Desativa uma empresa sem deletar seu registro.
     * Preserva integridade referencial com vagas, candidaturas e logs de
     * auditoria existentes (mesma decisão de UserService::deactivate).
     *
     * @param  Company $company Empresa resolvida via route model binding
     * @return void
     */
    public function deactivate(Company $company): void
    {
        $company->update([
            'is_active' => false,
        ]);
    }

    /**
     * Gera um slug único a partir do nome.
     * Normaliza via Str::slug (minúsculo, sem acento, URL-safe) e adiciona
     * sufixo numérico enquanto o slug já existir no banco.
     *
     * @param  string $name
     * @return string
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

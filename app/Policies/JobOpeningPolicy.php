<?php

namespace App\Policies;

use App\Models\JobOpening;
use App\Models\User;

class JobOpeningPolicy
{
    /**
     * Determina se o usuário pode listar as candidaturas de uma vaga.
     * Admin e recruiter listam de qualquer vaga; hiring-manager só das
     * vagas sob sua responsabilidade (via pivot job_opening_hiring_managers).
     *
     * Autoriza contra a vaga (não contra a candidatura) porque a ação é uma
     * listagem — não existe um recurso Application específico para autorizar.
     *
     * @param  User       $user
     * @param  JobOpening $jobOpening
     * @return bool
     */
    public function viewApplications(User $user, JobOpening $jobOpening): bool
    {
        if ($user->hasRole(['admin', 'recruiter']))
            return true;

        return $user->hasRole('hiring-manager')
            && $jobOpening->hiringManagers->contains($user->id);
    }
}

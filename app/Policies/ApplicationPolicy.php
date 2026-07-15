<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determina se o usuário pode visualizar a candidatura.
     * Admin e recruiter veem qualquer candidatura; hiring-manager só vê
     * as candidaturas das vagas sob sua responsabilidade.
     *
     * @param  User        $user
     * @param  Application $application
     * @return bool
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->hasRole(['admin', 'recruiter']))
            return true;

        return $user->hasRole('hiring-manager')
            && $application->job->hiringManagers->contains($user->id);
    }

    /**
     * Determina se o usuário pode mover a candidatura de etapa no pipeline.
     * Mesma regra de view — delega para ela: admin/recruiter em qualquer
     * candidatura, hiring-manager só nas vagas que gerencia.
     *
     * @param  User        $user
     * @param  Application $application
     * @return bool
     */
    public function move(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    /**
     * Determina se o usuário pode retirar (withdraw) a candidatura.
     * Apenas o próprio candidato dono da candidatura.
     *
     * @param  User        $user
     * @param  Application $application
     * @return bool
     */
    public function withdraw(User $user, Application $application): bool
    {
        return $user->id === $application->candidate_id;
    }
}

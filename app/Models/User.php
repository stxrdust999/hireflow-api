<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Representa um usuário do sistema, que pode ser candidato, recrutador ou administrador.
 *
 * Represents a system user, who can be a candidate, recruiter, or administrator.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'provider',
        'provider_id'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    /**
     * O usuário pode possuir vários papéis/perfis de acesso.
     *
     * The user can have many roles/access profiles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * O usuário (Hiring Manager) pode ser responsável por várias vagas.
     *
     * The user (Hiring Manager) can be responsible for multiple job openings.
     */
    public function managedJobs(): BelongsToMany
    {
        return $this->belongsToMany(JobOpening::class, 'job_opening_hiring_managers', 'user_id', 'job_opening_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representa um papel/perfil de acesso de usuário no sistema (ex: Admin, Recrutador, Candidato).
 *
 * Represents a user role/access profile in the system (e.g., Admin, Recruiter, Candidate).
 */
class Role extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug'
    ];
}

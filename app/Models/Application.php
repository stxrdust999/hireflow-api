<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Representa uma candidatura de um candidato a uma vaga de emprego.
 *
 * Represents a candidate's application for a job opening.
 */
class Application extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_id',
        'current_stage_id',
        'candidate_id',
        'resume_url'
    ];

    /**
     * Uma candidatura pertence a um usuário-candidato.
     *
     * An application belongs to a candidate user.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Uma candidatura pertence a uma vaga de emprego.
     *
     * An application belongs to a job opening.
     */
    public function job(): BelongsTo
    {
        return $this->BelongsTo(JobOpening::class);
    }

    /**
     * Uma candidatura pertence a uma etapa atual do processo seletivo da vaga.
     *
     * An application belongs to a current stage of the job stage.
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class, 'current_stage_id');
    }

    /**
     * Uma candidatura possui vários registros de histórico de mudança de etapa.
     *
     * An application has many stage transition logs.
     */
    public function stageLogs(): HasMany
    {
        return $this->hasMany(ApplicationStageLog::class);
    }

    /**
     * Uma candidatura possui vários comentários/anotações feitos por recrutadores.
     *
     * An application has many comments/notes made by recruiters.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

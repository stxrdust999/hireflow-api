<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Representa o histórico de movimentação de etapas de uma candidatura.
 *
 * Represents the history of stage transitions for an application.
 */
class ApplicationStageLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'stage_id',
        'moved_by',
    ];

    /**
     * O registro de mudança de etapa foi realizado por um usuário (recrutador).
     *
     * The stage transition was performed by a user (recruiter).
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    /**
     * O registro de histórico pertence a uma candidatura.
     *
     * The history log belongs to an application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * O registro de histórico está associado a uma etapa do processo seletivo.
     *
     * The history log is associated with a job stage.
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class);
    }
}

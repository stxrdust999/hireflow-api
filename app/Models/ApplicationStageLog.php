<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationStageLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'stage_id',
        'moved_by',
    ];

    /**
     * tem um autor de 'movimentação' de um passo pra outro
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    // um passo atual de vaga pertente, obviamente, a uma vaga
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // um passo de vaga possui um (por vez, has one) tipo de passo de vaga.
    public function stage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class);
    }
}

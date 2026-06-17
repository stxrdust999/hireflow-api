<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa uma etapa do processo seletivo de uma vaga de emprego.
 *
 * Represents a stage in the recruitment process of a job opening.
 */
class JobStage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_id',
        'name',
        'order'
    ];

    /**
     * A etapa do processo seletivo pertence a uma vaga de emprego.
     *
     * The recruitment stage belongs to a job opening.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }
}

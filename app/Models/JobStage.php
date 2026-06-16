<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_id',
        'name',
        'order'
    ];

    /**
     * A tabela era pra ser jobs pra lista de vagas, mas o Laravel
     * já tem uma tabela com esse nome, então tivemos que renomear pra JobOpening.
     * mas a JobOpening representa uma vaga.
     * 
     * portanto, a etapa de uma vaga (obviamente) pertence a uma vaga.
     * @return BelongsTo<JobOpening, JobStage>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }
}

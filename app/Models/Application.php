<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_id',
        'current_stage_id',
        'candidate_id',
        'resume_url'
    ];

    // uma aplicação de currículo pertence a um usuário-candidato.
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // uma aplicação de currículo pertence a uma vaga. não que a vaga seja dona dela.
    public function job(): BelongsTo
    {
        return $this->BelongsTo(JobOpening::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class, 'current_stage_id');
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(ApplicationStageLog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

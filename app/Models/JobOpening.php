<?php

namespace App\Models;

use App\Enums\JobOpeningEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa uma vaga de emprego aberta por uma empresa.
 *
 * Represents a job opening created by a company.
 */
class JobOpening extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'description',
        'location',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => JobOpeningEnum::class
    ];

    /**
     * Uma vaga pode ter vários Hiring Managers responsáveis por avaliar candidatos.
     *
     * A job opening can have multiple Hiring Managers responsible for evaluating candidates.
     */
    public function hiringManagers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_opening_hiring_managers', 'job_opening_id', 'user_id');
    }

    /**
     * Uma vaga de emprego foi criada por um usuário.
     *
     * A job opening was created by a user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Uma vaga possui várias etapas no processo seletivo.
     *
     * A job opening has many stages in the recruitment process.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(JobStage::class, 'job_id');
    }

    /**
     * Uma vaga pertence a uma empresa contratante.
     *
     * A job opening belongs to a hiring company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

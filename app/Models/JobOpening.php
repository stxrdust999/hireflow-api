<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobOpening extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'description',
        'location',
        'type'
    ];

    /**
     * Uma vaga tem um unico criador.
     * A job has only one creator.
     * @return HasOne<User, JobOpening>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(JobStage::class, 'job_id');
    }

    /**
     * Uma vaga pertence a uma empresa // uma empresa é dona de uma vaga.
     * A job belongs to a company // a company is owner of a job. :)
     * @return BelongsTo<Company, JobOpening>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

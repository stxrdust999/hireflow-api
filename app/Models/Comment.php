<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Representa um comentário ou anotação feito em uma candidatura por um recrutador.
 *
 * Represents a comment or note made on an application by a recruiter.
 */
class Comment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'author_id',
        'body'
    ];

    /**
     * O comentário pertence a uma candidatura.
     *
     * The comment belongs to an application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * O comentário foi escrito por um usuário (autor/recrutador).
     *
     * The comment was written by a user (author/recruiter).
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa uma notificação enviada a um usuário do sistema.
 *
 * Represents a notification sent to a system user.
 */
class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    /**
     * A notificação pertence a um usuário destinatário.
     *
     * The notification belongs to a recipient user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

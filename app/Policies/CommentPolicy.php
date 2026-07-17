<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determina se o usuário pode deletar o comentário.
     * Apenas o admin ou o próprio autor do comentário.
     *
     * @param  User    $user
     * @param  Comment $comment
     * @return bool
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($user->hasRole('admin'))
            return true;

        return $user->id === $comment->author_id;
    }
}

<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
    /**
     * Cria um novo comentário interno em uma candidatura.
     * Comentários são visíveis apenas para recrutadores e hiring managers —
     * candidatos não têm acesso.
     *
     * @param  array{
     *     application_id: string,
     *     author_id: int,
     *     comment: string
     * } $data
     * @return Comment
     */
    public function create(array $data): Comment
    {
        $comment = Comment::create([
            'application_id' => $data['application_id'],
            'author_id' => $data['author_id'],
            'body' => $data['comment']
        ]);

        return $comment;
    }

    /**
     * Deleta um comentário existente.
     * A autorização (quem pode deletar) é responsabilidade da Policy — não do Service.
     *
     * @param  Comment $comment Comentário resolvido via route model binding
     * @return void
     */
    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}

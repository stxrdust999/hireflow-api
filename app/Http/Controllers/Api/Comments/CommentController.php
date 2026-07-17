<?php

namespace App\Http\Controllers\Api\Comments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Application;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $service
    ) {
    }

    /**
     * Lista os comentários internos de uma candidatura.
     * Reutiliza a ApplicationPolicy::view para garantir que o hiring-manager
     * só veja comentários de candidaturas das próprias vagas.
     * Rota protegida (admin, recruiter, hiring-manager).
     *
     * @param  Application $application Candidatura resolvida pelo UUID na rota
     * @return JsonResponse 200 com a coleção de comentários
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não puder ver a candidatura
     */
    public function index(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $comments = Comment::with('author')
            ->where('application_id', $application->id)
            ->get();

        return CommentResource::collection($comments)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cria um comentário interno numa candidatura.
     * author_id vem do usuário autenticado e application_id da URL (rota aninhada);
     * apenas o texto (comment) vem do corpo. Reutiliza a ApplicationPolicy::view.
     * Rota protegida (admin, recruiter, hiring-manager).
     *
     * @param  StoreCommentRequest $request
     * @param  Application $application Candidatura resolvida pelo UUID na rota
     * @return JsonResponse 201 com o comentário criado
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não puder comentar na candidatura
     */
    public function store(StoreCommentRequest $request, Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $comment = $this->service->create([
            'application_id' => $application->id,
            'author_id' => Auth::id(),
            'comment' => $request->validated('comment')
        ]);

        $comment->load('author');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove um comentário.
     * A CommentPolicy::delete garante que apenas o admin ou o autor do
     * comentário possam deletá-lo.
     * Rota protegida (admin, recruiter, hiring-manager).
     *
     * @param  Comment $comment Comentário resolvido pelo UUID na rota
     * @return JsonResponse 204 sem corpo
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se não for admin nem autor
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->service->delete($comment);

        return response()->json(null, 204);
    }
}

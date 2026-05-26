<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Ajoute un commentaire à une tâche.
     * Seuls les membres du workspace peuvent commenter.
     */
    public function store(StoreCommentRequest $request, Task $task): RedirectResponse
    {
        // On réutilise la TaskPolicy::view pour vérifier que l'user est membre du workspace
        $this->authorize('view', $task);

        $task->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Commentaire ajouté.');
    }

    /**
     * Supprime un commentaire.
     * Route shallow : seul l'auteur peut supprimer.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $task = $comment->task;
        $comment->delete();

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Commentaire supprimé.');
    }
}

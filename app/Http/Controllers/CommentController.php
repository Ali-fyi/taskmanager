<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Adds a comment to a task.
     * Only workspace members can comment.
     */
    public function store(StoreCommentRequest $request, Task $task): RedirectResponse
    {
        // Reuse TaskPolicy::view to verify the user is a member of the workspace
        $this->authorize('view', $task);

        $task->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Comment added.');
    }

    /**
     * Deletes a comment.
     * Shallow route: only the author can delete.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $task = $comment->task;
        $comment->delete();

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Comment deleted.');
    }
}

<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Seul l'auteur peut supprimer son commentaire.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}

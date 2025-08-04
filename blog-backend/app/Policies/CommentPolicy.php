<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;


class CommentPolicy
{
    // 全てのユーザーがコメントを閲覧可能
    public function viewAny(?User $user)
    {
        return true;
    }

    public function create(?User $user)
    {
        // 全てのユーザーがコメントを作成可能
        return true;
    }

    public function update(User $user, Comment $comment)
    {
         // コメント作成者のみ更新可能
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment)
    {
        // 作成者、コメントの作成者または管理者はコメント削除可能
        return $user->isAdmin()
            || $user->id === $comment->user_id
            || $user->id === $comment->post->user_id;
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;

class PostPolicy
{
    // 全てのユーザーが記事を閲覧可能
    public function viewAny(?User $user)
    {
        return true;
    }

    public function view(?User $user, Post $post)
    {
        // 公開済みの記事は誰でも閲覧可能
        if($post->is_published){
            return true;
        }

        // 下書きは作成者のみ閲覧可能
        return $user && $user->id === $post->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    // 更新時は、作成者または管理者は更新可能
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    // 削除時は、作成者または管理者は削除可能
    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }
}

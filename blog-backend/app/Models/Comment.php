<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'author_name',
        'content',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // コメントした人が認証されたユーザーもしくはゲストユーザー
    public function getAuthorName()
    {
        return $this->user ? $this->user->name : $this->author_name;
    }

    // コメントがゲストユーザーによるものかどうかを判定
    public function getGuest()
    {
        return is_null($this->user_id);
    }
}

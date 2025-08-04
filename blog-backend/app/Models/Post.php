<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    // テーブル名
    protected $table = 'posts';

    // フィールド
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'status',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 公開を選択時のフィルタリング
    public function scopePublished(Builder $query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    // 下書き選択時のフィルタリング
    public function scopeDrafts(Builder $query)
    {
        return $query->where('status', 'draft');
    }

    // カテゴリ別のフィルタリング
    public function scopeWhereCategory(Builder $query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // タイトルの部分一致検索のロジック
    public function scopeSearch(Builder $query, string $search)
    {
        return $query->where('title', 'like', '%' . $search . '%');
    }

    // 公開済みの投稿を取得するスコープ
    public function scopeGetIsPublished()
    {
        return $this->status === 'published' && $this->published <= now();
    }
}

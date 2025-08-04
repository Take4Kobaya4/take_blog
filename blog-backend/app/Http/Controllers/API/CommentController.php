<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
        // 公開されていない記事は、404でコメント不可
        if(!$post->is_published){
            return response()->json(['message' => 'Post not allowed on this post'], 404);
        }

        $rules = [
            'content' => 'required|string',
        ];

        // 未ログインユーザーは名前の入力必須
        if(!$request->user()){
            $rules['name'] = 'required|string';
        }

        $request->validate($rules);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user() ? $request->user()->id : null,
            'author_name' => $request->user() ? null : $request->author_name,
            'content' => $request->content,
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'message' => 'Comment created successfully',
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}

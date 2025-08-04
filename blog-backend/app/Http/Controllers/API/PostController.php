<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::query();
        // 未ログインユーザーは公開済みを表示
        if(!$request->user()){
            $query->published();
        } else {
            // statusを指定している場合はそのステータスの記事を表示
            $status = $request->input('status');
            if($status === 'draft'){
                $query->drafts();
            } elseif($status === 'published') {
                $query->published();
            }
            // statusを指定していない場合は下書き・公開済みの両方を表示
        }

        // 部分一致
        if($request->has('search')){
            $query->search($request->input('search'));
        }

        // カテゴリの絞り込み
        if($request->has('category_id')) {
            $query->whereCategory($request->input('category_id'));
        }

        $posts = $query->with('category')->latest()->paginate(10);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'required_if:status,published|date',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'user_id' => $request->user()->id,
            // ステータスを公開にした時、公開日が入る
            'published_at' => $request->status === 'published' ? ($request->published_at ?? now()) : null,
        ]);

        $post->load(['user:id, name', 'category:id,name']);

        return response()->json([
            'message' => 'Post created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post)
    {
        // 未ログインユーザーは公開済みの記事を表示
        if(!$post->is_published && (!$request->user() || $post->user_id !== $request->user()->id)){
            return response()->json(['message' => 'Post not found'], 403);
        }

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'required_if:status,published|date',
        ]);

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'status' => $request->status,
            // ステータスを公開にした時、公開日が入る
            'published_at' => $request->status === 'published' ? ($request->published_at ?? now()) : null,
        ]);

        return response()->json([
            'message' => 'Post updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function myPosts(Request $request)
    {
        $query = Post::where('user_id', $request->user()->id)
            ->with('category')
            ->latest();

        // statusの選択によって絞り込み検索を可能にする
        if($request->filled('status')){
            if($request->status === 'published'){
                $query->published();
            } elseif($request->status === 'draft') {
                $query->drafts();
            }
        }

        $posts = $query->paginate(10);

        return response()->json($posts);
    }
}

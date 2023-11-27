<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\UploadMediaRequest;
use App\Post;
use App\User;
use Illuminate\Support\Str;

class PostController extends Controller
{

    private $post;
    private $user;

    public function __construct(Post $post, User $user)
    {
        $this->post = $post;
        $this->user = $user;
    }

    public function index()
    {
        return $this->post->query()
            ->with(['user', 'media'])
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function show(Post $post)
    {
        return $post->load(['user', 'media']);
    }

    public function store(UploadMediaRequest $request)
    {
        $post = $this->post->create([
            "title" => 'title ' . Str::random(10),
            "content" => 'content ' . Str::random(100),
            "user_id" => $this->user->all('id')->random()->first()->id,
        ]);

        $post->addMedia($request->file('image'));

        return response()->json([
            "message" => "Post created successfully",
            "post" => $post->load(['user', 'media']),
            "total_size" => $post->mediaTotalSize(),
        ]);
    }

    public function update(Post $post, UploadMediaRequest $request)
    {
        $post->update([
            "title" => 'title ' . Str::random(10),
            "content" => 'content ' . Str::random(100),
            "user_id" => $this->user->all('id')->random()->first()->id,
        ]);

        $post->syncMedia($request->file('image'));

        return response()->json([
            "message" => "Post updated successfully",
            "post" => $post->load(['user', 'media']),
            "total_size" => $post->mediaTotalSize(),
        ]);
    }

    public function destroy(Post $post)
    {
        $post->deleteMedia();
        $post->delete();

        return response()->json([
            "message" => "Post deleted successfully",
        ]);
    }
}

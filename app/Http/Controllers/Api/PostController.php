<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:png,jpg,gif,png|max:2048',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts/', $image->hashName());

        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        return new PostResource(true, "Create succesfully", $post);
    }

    public function show(Post $post)
    {
        return new PostResource(true, "Data Post Ditemukan", $post);
    }

    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts/', $image->hashName());
            Storage::delete('public/posts/' . $post->image);
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        } else {
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        return new PostResource(true, "Data Post Berhasil Diupdate", $post);
    }

    public function destroy(Post $post)
    {
        Storage::delete('public/posts/' . $post->image);
        $post->delete();
        return new PostResource(true, "Data Post Berhasil Dihapus", $post);
    }
}

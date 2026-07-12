<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller {
    public function index() {
        $posts = Post::latest()->get();
        return view('posts.index', compact('posts'));
    }
    public function create() {
        return view('posts.create');
    }
    public function store(Request $request) {
        $validatedData = $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        Post::create($validatedData);

        return redirect()->route('posts.index');
    }
    public function show($id) {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }
    public function edit($id) {
        $post = Post::findOrFail($id);
        return view('posts.edit', compact('post'));
    }
    public function update(Request $request, $id) {
            $post = Post::findOrFail($id);
            $validatedData = $request->validate([
                'title' => 'required',
                'content' => 'required',
            ]);
            $post->update($validatedData);
            return redirect()->route('posts.show', $post->id);
    }
    public function destroy($id) {
        $post = Post::findOrFail($id);
        $post->delete();
        return redirect()->route('posts.index');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $response = $this->get('/posts');

        $response->assertOk();
    }

    public function test_store(): void
    {
        $response = $this->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $response->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);
    }

    public function test_update(): void
    {
        $post = $this->makePost();

        $response = $this->put('/posts/' . $post->id, [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response->assertRedirect('/posts/' . $post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);
    }

    public function test_destroy(): void
    {
        $post = $this->makePost();

        $response = $this->delete('/posts/' . $post->id);

        $response->assertRedirect('/posts');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    private function makePost(): Post
    {
        return Post::create([
            'title' => 'Sample Title',
            'content' => 'Sample Content',
        ]);
    }
}
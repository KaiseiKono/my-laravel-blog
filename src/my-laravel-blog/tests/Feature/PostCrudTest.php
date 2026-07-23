<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    // ---------- 閲覧(ゲストも可) ----------

    public function test_get_index(): void
    {
        $response = $this->get('/posts');

        $response->assertOk();
    }

    public function test_guest_can_view_post_index_and_show(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $this->get('/posts')->assertOk();

        $showResponse = $this->get('/posts/' . $post->id);
        $showResponse->assertOk();
        $showResponse->assertSee($post->title);
        $showResponse->assertSee($post->content, false);
    }

    public function test_get_show(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->get('/posts/' . $post->id);

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee($post->content, false);
    }

    // ---------- 作成(ログイン必須) ----------

    public function test_get_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/posts/create');

        $response->assertOk();
    }

    public function test_store(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $response->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => '<p>Test Content</p>',
        ]);
    }

    public function test_authenticated_user_can_create_post_and_is_saved_as_author(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Authored Post',
            'content' => 'Authored Content',
        ]);

        $response->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'title' => 'Authored Post',
            'content' => '<p>Authored Content</p>',
            'user_id' => $user->id,
        ]);
    }

    public function test_store_when_title_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/posts/create')
            ->post('/posts', [
                'title' => '',
                'content' => 'Test Content',
            ]);

        $response->assertRedirect('/posts/create');
        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_store_when_content_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/posts/create')
            ->post('/posts', [
                'title' => 'Test Title',
                'content' => '',
            ]);

        $response->assertRedirect('/posts/create');
        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_store_keep_title_when_only_content_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/posts/create')
            ->post('/posts', [
                'title' => 'Kept Title',
                'content' => '',
            ]);

        $response->assertSessionHasErrors('content');
        $response->assertSessionHasInput('title', 'Kept Title');
    }

    public function test_store_when_title_exceeds_max_length(): void
    {
        $user = User::factory()->create();
        $tooLongTitle = str_repeat('a', 256);

        $response = $this->actingAs($user)
            ->from('/posts/create')
            ->post('/posts', [
                'title' => $tooLongTitle,
                'content' => 'Test Content',
            ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('posts', 0);
    }

    // ---------- 編集(投稿者本人のみ) ----------

    public function test_get_edit(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->actingAs($user)->get('/posts/' . $post->id . '/edit');

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee($post->content, false);
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->actingAs($user)->put('/posts/' . $post->id, [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response->assertRedirect('/posts/' . $post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => '<p>Updated Content</p>',
        ]);
    }

    public function test_update_when_title_is_empty(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->actingAs($user)
            ->from('/posts/' . $post->id . '/edit')
            ->put('/posts/' . $post->id, [
                'title' => '',
                'content' => 'Updated Content',
            ]);

        $response->assertRedirect('/posts/' . $post->id . '/edit');
        $response->assertSessionHasErrors('title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Sample Title',
            'content' => 'Sample Content',
        ]);
    }

    public function test_update_keep_input_after_validation_failure(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->actingAs($user)
            ->from('/posts/' . $post->id . '/edit')
            ->put('/posts/' . $post->id, [
                'title' => '',
                'content' => 'Edited Content Kept',
            ]);

        $response->assertSessionHasErrors('title');
        $response->assertSessionHasInput('content', 'Edited Content Kept');
    }

    public function test_update_when_title_exceeds_max_length(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);
        $tooLongTitle = str_repeat('b', 256);

        $response = $this->actingAs($user)->put('/posts/' . $post->id, [
            'title' => $tooLongTitle,
            'content' => 'Edited Content',
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Sample Title',
        ]);
    }

    public function test_owner_can_edit_and_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $this->actingAs($user)
            ->get('/posts/' . $post->id . '/edit')
            ->assertOk();

        $this->actingAs($user)
            ->put('/posts/' . $post->id, [
                'title' => 'Updated By Owner',
                'content' => 'Updated Content By Owner',
            ])
            ->assertRedirect('/posts/' . $post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated By Owner',
        ]);

        $this->actingAs($user)
            ->delete('/posts/' . $post->id)
            ->assertRedirect('/posts');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    // ---------- 削除(投稿者本人のみ) ----------

    public function test_destroy(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $response = $this->actingAs($user)->delete('/posts/' . $post->id);

        $response->assertRedirect('/posts');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    // ---------- 未ログインでは作成・編集・削除できない ----------

    public function test_guest_cannot_create_edit_delete_post(): void
    {
        $user = User::factory()->create();
        $post = $this->makePost($user);

        $this->get('/posts/create')->assertRedirect('/login');

        $this->post('/posts', [
            'title' => 'Guest Post',
            'content' => 'Guest Content',
        ])->assertRedirect('/login');

        $this->assertDatabaseMissing('posts', [
            'title' => 'Guest Post',
        ]);

        $this->get('/posts/' . $post->id . '/edit')->assertRedirect('/login');

        $this->put('/posts/' . $post->id, [
            'title' => 'Hacked Title',
            'content' => 'Hacked Content',
        ])->assertRedirect('/login');

        $this->delete('/posts/' . $post->id)->assertRedirect('/login');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $post->title,
        ]);
    }

    // ---------- 投稿者以外は編集・更新・削除できない ----------

    public function test_other_user_cannot_edit_update_or_delete_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = $this->makePost($owner);

        $this->actingAs($otherUser)
            ->get('/posts/' . $post->id . '/edit')
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put('/posts/' . $post->id, [
                'title' => 'Hijacked Title',
                'content' => 'Hijacked Content',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete('/posts/' . $post->id)
            ->assertForbidden();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $post->title,
        ]);
    }

    // ---------- XSS対策 ----------

    public function test_dangerous_script_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'XSS Test',
            'content' => '<script>alert(1)</script>本文',
        ]);

        $response->assertRedirect('/posts');

        $post = Post::where('title', 'XSS Test')->firstOrFail();

        $this->assertStringNotContainsString('<script>', $post->content);

        $showResponse = $this->get('/posts/' . $post->id);
        $showResponse->assertOk();
        $showResponse->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_dangerous_event_attribute(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Event Attribute Test',
            'content' => '<p onclick="alert(1)">Click me</p>',
        ]);

        $response->assertRedirect('/posts');

        $post = Post::where('title', 'Event Attribute Test')->firstOrFail();

        $this->assertStringNotContainsString('onclick=', $post->content);

        $showResponse = $this->get('/posts/' . $post->id);
        $showResponse->assertOk();
        $showResponse->assertDontSee('onclick="alert(1)"', false);
    }

    public function test_dangerous_javascript_protocol(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'JavaScript Protocol Test',
            'content' => '<a href="javascript:alert(1)">Click me</a>',
        ]);

        $response->assertRedirect('/posts');

        $post = Post::where('title', 'JavaScript Protocol Test')->firstOrFail();

        $this->assertStringNotContainsString('javascript:', $post->content);

        $showResponse = $this->get('/posts/' . $post->id);
        $showResponse->assertOk();
        $showResponse->assertDontSee('href="javascript:alert(1)"', false);
    }

    private function makePost(User $user): Post
    {
        return Post::create([
            'title' => 'Sample Title',
            'content' => 'Sample Content',
            'user_id' => $user->id,
        ]);
    }
}
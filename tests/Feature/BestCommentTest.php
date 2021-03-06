<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BestCommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_thread_creator_can_mark_any_comment_as_best_comment(){
        $this->signIn();

        $thread = create('App\Thread', ['best_comment_id' => 1, 'user_id' => auth()->id()]);

        $comment1 = create('App\Comment', ['thread_id' => $thread->id]);
        $comment2 = create('App\Comment', ['thread_id' => $thread->id]);

        $this->post("/comments/{$comment1->id}/best")
            ->assertStatus(204);

        $this->assertTrue($comment1->isBest);

        $this->assertEquals($comment1->id, $thread->best_comment_id);

        $this->post("/comments/{$comment2->id}/best")
            ->assertStatus(204);

        $this->assertEquals($comment2->id, $thread->fresh()->best_comment_id);
        $this->assertTrue($comment2->isBest);

    }

    /** @test */
    function a_thread_creator_can_remove_a_best_comment(){

        $this->signIn();

        $thread = create('App\Thread', ['best_comment_id' => 1, 'user_id' => auth()->id()]);

        $comment = create('App\Comment', ['thread_id' => $thread->id]);

        $this->post("/comments/{$comment->id}/best");

        $this->assertEquals($comment->id, $thread->best_comment_id);

        $this->delete("/comments/{$comment->id}/best")
            ->assertStatus(204);

        $this->assertEquals(null, $thread->fresh()->best_comment_id);
    }

    /** @test */
    function only_thread_creator_can_add_best_comment(){
        $this->signIn();

        $user = create('App\User');

        $thread = create('App\Thread', ['user_id' => $user->id]);

        $comment = create('App\Comment', ['thread_id' => $thread->id]);

        $this->post("/comments/{$comment->id}/best")
            ->assertStatus(403);

        $this->assertFalse($comment->isBest);

    }

    /** @test */
    function only_thread_creator_can_remove_best_comment(){
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $comment = create('App\Comment', ['thread_id' => $thread->id]);

        $this->post("/comments/{$comment->id}/best")
            ->assertStatus(204);

        $user = create('App\User');

        $this->signIn($user);

        $this->delete("/comments/{$comment->id}/best")
            ->assertStatus(403);
    }

    /** @test */
    function if_the_best_comment_deleted_then_the_thread_will_updated_to_reflect_this(){

        $this->signIn();

        $comment = create('App\Comment', ['user_id' => auth()->id()]);

        $comment->markAsBest();

        $this->assertEquals($comment->thread->best_comment_id, $comment->id);

        $this->delete(route("comments.destroy", $comment->id));

        $this->assertNull($comment->thread->fresh()->best_comment_id);
    }
}

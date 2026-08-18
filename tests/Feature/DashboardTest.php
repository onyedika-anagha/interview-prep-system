<?php

use App\Models\Attempt;
use App\Models\Topic;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertOk();
});

test('dashboard reports overall accuracy and review queue size', function () {
    $this->actingAs(User::factory()->create());

    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
        'review_due_at' => now()->subDay(),
    ]);
    Attempt::create(['question_id' => $question->id, 'submitted_answer' => 'a', 'is_correct' => true, 'score' => 100]);
    Attempt::create(['question_id' => $question->id, 'submitted_answer' => 'b', 'is_correct' => false, 'score' => 0]);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('overall.total_attempts', 2)
            ->where('overall.accuracy', 50)
            ->where('overall.review_due_count', 1)
            ->has('topics', 1));
});
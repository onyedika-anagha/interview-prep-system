<?php

use App\Models\Question;
use App\Models\Topic;
use App\Services\ReviewQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeQuestion(int $bucket = 1): Question
{
    $topic = Topic::create(['name' => 'Test Topic', 'category' => 'general', 'description' => 'x']);

    return Question::create([
        'topic_id' => $topic->id,
        'type' => 'short_answer',
        'difficulty' => 'easy',
        'prompt' => 'x',
        'reference_answer' => 'y',
        'status' => 'approved',
        'generated_by' => 'manual',
        'review_bucket' => $bucket,
    ]);
}

it('demotes to bucket 1 on a wrong answer, due almost immediately', function () {
    $question = makeQuestion(bucket: 3);

    (new ReviewQueue)->recordResult($question, false);

    expect($question->review_bucket)->toBe(1)
        ->and($question->review_due_at->isBefore(now()->addDays(2)))->toBeTrue();
});

it('promotes the bucket on a correct answer, pushing the due date further out', function () {
    $question = makeQuestion(bucket: 1);

    (new ReviewQueue)->recordResult($question, true);

    expect($question->review_bucket)->toBe(2)
        ->and($question->review_due_at->isAfter(now()->addDay()))->toBeTrue();
});

it('caps the bucket at the maximum instead of promoting forever', function () {
    $question = makeQuestion(bucket: 5);

    (new ReviewQueue)->recordResult($question, true);

    expect($question->review_bucket)->toBe(5);
});

it('schedules a longer delay for higher buckets', function () {
    $lowBucket = makeQuestion(bucket: 1);
    $highBucket = makeQuestion(bucket: 4);

    $queue = new ReviewQueue;
    $queue->recordResult($lowBucket, true);
    $queue->recordResult($highBucket, true);

    expect($lowBucket->review_due_at->isBefore($highBucket->review_due_at))->toBeTrue();
});

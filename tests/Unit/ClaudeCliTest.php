<?php

use App\Models\Question;
use App\Models\Topic;
use App\Services\ClaudeCli;
use Illuminate\Support\Facades\Process;

it('parses a successful JSON response into an array', function () {
    $questions = json_encode([
        ['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'What is PHP?', 'reference_answer' => 'A language', 'test_cases' => null],
    ]);

    Process::fake([
        '*' => Process::result(output: json_encode(['result' => $questions])),
    ]);

    $topic = new Topic(['name' => 'PHP', 'description' => 'PHP basics']);

    $result = (new ClaudeCli)->generateQuestions($topic, 'easy', 1);

    expect($result)->toBeArray()->toHaveCount(1)
        ->and($result[0]['type'])->toBe('mcq');
});

it('throws when the process exits non-zero', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'boom', exitCode: 1),
    ]);

    $question = new Question(['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x', 'reference_answer' => 'y']);

    (new ClaudeCli)->gradeAnswer($question, 'my answer');
})->throws(RuntimeException::class, 'Claude CLI failed');

it('throws when the CLI output is not valid JSON', function () {
    Process::fake([
        '*' => Process::result(output: 'not json'),
    ]);

    $question = new Question(['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x', 'reference_answer' => 'y']);

    (new ClaudeCli)->gradeAnswer($question, 'my answer');
})->throws(RuntimeException::class, 'unexpected response shape');

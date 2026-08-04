<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;

interface AiProvider
{
    /**
     * @return array<int, array{type: string, difficulty: string, prompt: string, reference_answer: string, test_cases: array|null}>
     */
    public function generateQuestions(Topic $topic, string $difficulty, int $count): array;

    /**
     * @return array{is_correct: bool, score: int, feedback: string}
     */
    public function gradeAnswer(Question $question, string $answer): array;

    public function explainCodeResult(Question $question, string $code, array $executionResult): string;
}

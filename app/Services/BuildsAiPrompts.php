<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;
use RuntimeException;

/**
 * Shared prompt templates and response parsing for AiProvider implementations.
 */
trait BuildsAiPrompts
{
    protected function generateQuestionsPrompt(Topic $topic, string $difficulty, int $count): string
    {
        return <<<PROMPT
        Generate {$count} interview practice questions for the topic "{$topic->name}" ({$topic->description}) at "{$difficulty}" difficulty.

        Return ONLY a JSON array (no prose, no markdown fences). Each element must have:
        - "type": one of "mcq", "short_answer", "coding"
        - "difficulty": string, echoing "{$difficulty}"
        - "prompt": the question text (for mcq, include the options inline)
        - "reference_answer": the correct answer or solution
        - "language": for "coding" questions only, either "javascript" or "php"; null for other types
        - "test_cases": for "coding" questions only, an array of {"input": ..., "expected_output": ...} objects; null for other types

        For "coding" questions, the reference_answer must be a full script that reads a single JSON-encoded value from stdin and prints a single JSON-encoded result to stdout (no extra output) — this is how submissions get executed and checked against test_cases.
        PROMPT;
    }

    protected function gradeAnswerPrompt(Question $question, string $answer): string
    {
        return <<<PROMPT
        Question ({$question->type}, {$question->difficulty}): {$question->prompt}

        Reference answer: {$question->reference_answer}

        Submitted answer: {$answer}

        Grade the submitted answer against the reference answer. Return ONLY a JSON object (no prose, no markdown fences) with:
        - "is_correct": boolean
        - "score": integer 0-100
        - "feedback": short explanation of what's right or wrong
        PROMPT;
    }

    protected function explainCodeResultPrompt(Question $question, string $code, array $executionResult): string
    {
        $results = json_encode($executionResult);

        return <<<PROMPT
        Question: {$question->prompt}

        Submitted code:
        {$code}

        Test execution results (JSON): {$results}

        In plain language, explain which test cases failed and why, and how to fix the code. Do not repeat the full code back; focus on the explanation.
        PROMPT;
    }

    /**
     * @return array<mixed>
     */
    protected function parseJsonResponse(string $raw): array
    {
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($raw)) ?? $raw;

        $decoded = json_decode(trim($cleaned), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("AI provider returned invalid JSON: {$raw}");
        }

        return $decoded;
    }
}

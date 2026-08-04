<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic_slug' => 'required|exists:topics,slug',
            'type' => 'required|in:mcq,short_answer,coding',
            'difficulty' => 'required|string',
            'prompt' => 'required|string',
            'reference_answer' => 'required|string',
            'language' => 'nullable|in:javascript,php',
            'test_cases' => 'nullable|array',
            'status' => 'nullable|in:draft,approved',
            'generated_by' => 'nullable|string',
        ]);

        $topic = Topic::where('slug', $validated['topic_slug'])->firstOrFail();

        $question = $topic->questions()->create([
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'prompt' => $validated['prompt'],
            'reference_answer' => $validated['reference_answer'],
            'language' => $validated['language'] ?? null,
            'test_cases' => $validated['test_cases'] ?? null,
            'status' => $validated['status'] ?? 'approved',
            'generated_by' => $validated['generated_by'] ?? 'claude',
        ]);

        return response()->json(['question' => $question], 201);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:mcq,short_answer,coding',
            'difficulty' => 'sometimes|string',
            'prompt' => 'sometimes|string',
            'reference_answer' => 'sometimes|string',
            'language' => 'sometimes|nullable|in:javascript,php',
            'test_cases' => 'sometimes|nullable|array',
            'status' => 'sometimes|in:draft,approved',
        ]);

        $question->update($validated);

        return response()->json(['question' => $question]);
    }
}

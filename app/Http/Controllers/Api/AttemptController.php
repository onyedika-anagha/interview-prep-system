<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic_slug' => 'nullable|exists:topics,slug',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $attempts = Attempt::query()
            ->with(['question:id,topic_id,type,prompt', 'question.topic:id,name,slug'])
            ->when($validated['topic_slug'] ?? null, function ($query, $slug) {
                $query->whereHas('question.topic', fn ($q) => $q->where('slug', $slug));
            })
            ->latest()
            ->limit($validated['limit'] ?? 20)
            ->get(['id', 'question_id', 'is_correct', 'score', 'feedback', 'created_at']);

        return response()->json(['attempts' => $attempts]);
    }
}

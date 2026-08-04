<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic_slug' => 'required|exists:topics,slug',
            'title' => 'required|string',
            'content' => 'nullable|string',
            'url' => 'nullable|url',
            'generated_by' => 'nullable|string',
        ]);

        $topic = Topic::where('slug', $validated['topic_slug'])->firstOrFail();

        $resource = $topic->resources()->create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'url' => $validated['url'] ?? null,
            'generated_by' => $validated['generated_by'] ?? 'claude',
        ]);

        return response()->json(['resource' => $resource], 201);
    }
}

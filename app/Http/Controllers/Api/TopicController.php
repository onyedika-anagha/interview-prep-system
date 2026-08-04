<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;

class TopicController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'topics' => Topic::orderBy('name')->get(['id', 'name', 'slug', 'category', 'description']),
        ]);
    }
}

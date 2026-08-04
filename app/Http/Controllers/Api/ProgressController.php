<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProgressStats;
use Illuminate\Http\JsonResponse;

class ProgressController extends Controller
{
    public function index(ProgressStats $stats): JsonResponse
    {
        return response()->json(['progress' => $stats->perTopic()]);
    }
}

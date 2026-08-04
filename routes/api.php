<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\TopicController;
use Illuminate\Support\Facades\Route;

// Local-only API for the MCP server (see mcp-server/). This app is local and
// single-user (PRD FR14) — no auth here, only ever called from localhost by
// the MCP process, never exposed publicly. All slug generation and model
// validation stays here so the MCP server never touches the database directly.
Route::get('topics', [TopicController::class, 'index']);
Route::post('questions', [QuestionController::class, 'store']);
Route::patch('questions/{question}', [QuestionController::class, 'update']);
Route::post('resources', [ResourceController::class, 'store']);
Route::get('attempts', [AttemptController::class, 'index']);
Route::get('progress', [ProgressController::class, 'index']);

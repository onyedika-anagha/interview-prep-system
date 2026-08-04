<?php

namespace App\Http\Controllers;

use App\Services\ProgressStats;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function index(ProgressStats $stats): Response
    {
        return Inertia::render('progress/index', [
            'stats' => $stats->perTopic(),
        ]);
    }
}

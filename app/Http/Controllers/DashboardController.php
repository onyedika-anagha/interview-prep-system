<?php

namespace App\Http\Controllers;

use App\Services\ProgressStats;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(ProgressStats $stats): Response
    {
        return Inertia::render('dashboard', [
            'overall' => $stats->overall(),
            'topics' => $stats->perTopic(),
        ]);
    }
}

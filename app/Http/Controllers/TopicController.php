<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Inertia\Inertia;
use Inertia\Response;

class TopicController extends Controller
{
    public function index(): Response
    {
        $topics = Topic::orderBy('name')->get(['id', 'name', 'slug', 'category', 'description'])->groupBy('category');

        return Inertia::render('topics/index', [
            'topics' => [
                'stack' => $topics->get('stack', collect())->values(),
                'general' => $topics->get('general', collect())->values(),
            ],
        ]);
    }
}

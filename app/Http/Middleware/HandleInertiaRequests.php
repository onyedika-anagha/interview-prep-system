<?php

namespace App\Http\Middleware;

use App\Models\Question;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            // Lazy: resolved when the response is built, after route middleware (e.g.
            // AutoLoginLocalUser) has run — not here, while this global middleware executes,
            // which is before route-specific middleware and would see the pre-login user.
            'auth' => fn () => ['user' => $request->user()],
            'draftQuestionCount' => fn () => $request->user() ? Question::where('status', 'draft')->count() : 0,
        ]);
    }
}

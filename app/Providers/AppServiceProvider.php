<?php

namespace App\Providers;

use App\Services\AiProvider;
use App\Services\ClaudeCli;
use App\Services\GeminiApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiProvider::class, function () {
            return match (config('services.ai.provider')) {
                'gemini' => new GeminiApi,
                default => new ClaudeCli,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

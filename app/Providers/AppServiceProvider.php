<?php

namespace App\Providers;

use App\Services\WinnowingService;
use App\Services\JaccardBloomService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WinnowingService::class, function () {
            return new WinnowingService(
                kgramSize:  (int) env('WINNOWING_KGRAM_SIZE', 3),
                windowSize: (int) env('WINNOWING_WINDOW_SIZE', 3)
            );
        });

        $this->app->singleton(JaccardBloomService::class, function () {
            return new JaccardBloomService(
                ngramSize: (int) env('JACCARD_NGRAM_SIZE', 2),
                bloomSize: (int) env('JACCARD_BLOOM_SIZE', 512),
                hashCount: (int) env('JACCARD_HASH_COUNT', 3)
            );
        });
    }

    public function boot(): void {}
}
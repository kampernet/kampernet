<?php

namespace Kampernet\Kampernet\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Kampernet\Kampernet\Application\Commands\Generate;
use Kampernet\Kampernet\Application\Commands\Initialize;

class KampernetServiceProvider extends ServiceProvider {

    public function boot() {

        if ($this->app->runningInConsole()) {
            $this->commands([
                Initialize::class,
                Generate::class,
            ]);
        }
    }
}

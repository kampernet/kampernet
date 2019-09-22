<?php

namespace Kampernet\Kampernet\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Kampernet\Kampernet\Application\Commands\Generate;

class KampernetServiceProvider extends ServiceProvider {

    public function boot() {

        if ($this->app->runningInConsole()) {
            $this->commands([
                Generate::class,
            ]);
        }
    }
}

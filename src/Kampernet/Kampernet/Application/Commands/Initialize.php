<?php

namespace Kampernet\Kampernet\Application\Commands;

use Illuminate\Console\Command;

class Initialize extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kampernet:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize a new Kampernet style Laravel app';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $path = realpath(base_path());
        chdir($path);
        copy("$path/vendor/kampernet/kampernet/templates/example.yml", "$path/kampernet.yml");
        shell_exec("composer require laravel/passport martinlindhe/laravel-vue-i18n-generator laravel/ui");
        shell_exec("composer require barryvdh/laravel-ide-helper --dev");
        shell_exec("./artisan migrate");
        shell_exec("./artisan passport:keys");
        shell_exec("./artisan ui vue --auth --no-interaction");
        $this->info("All done, now modify the kampernet.yml file to suit your app and run artisan kampernet:generate");
    }
}

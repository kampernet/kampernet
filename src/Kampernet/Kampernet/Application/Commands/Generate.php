<?php

namespace Kampernet\Kampernet\Application\Commands;

use Illuminate\Console\Command;

class Generate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kampernet:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and sync classes from /app.yml file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $this->info("here we will generate and sync.");
    }
}

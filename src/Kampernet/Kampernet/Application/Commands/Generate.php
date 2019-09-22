<?php

namespace Kampernet\Kampernet\Application\Commands;

use Illuminate\Console\Command;

class Generate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kampernet:generate {root=src}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate classes from /kampernet.yml file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $this->info("here we will generate and sync.");
    }
}

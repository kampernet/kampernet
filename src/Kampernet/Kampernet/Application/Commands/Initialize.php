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

        $this->info("here we will initialize the app.");
    }
}

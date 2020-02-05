<?php

namespace Kampernet\Kampernet\Application\Commands;

use Illuminate\Console\Command;
use Kampernet\Kampernet\Domain\Services\CodeGenerator;
use Symfony\Component\Yaml\Yaml;

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
    protected $description = 'Generate classes from /kampernet.yml file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $path = realpath(base_path());
        $generator = new CodeGenerator(Yaml::parseFile("$path/kampernet.yml"), $path);
        try {
            $generator->writeBoilerPlate();
            // add this to the composer.json
            //  "psr-0": {
            //      "Kampernet\\Wrench": "src"
            //  },
            $this->info("Successfully wrote boilerplate.");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

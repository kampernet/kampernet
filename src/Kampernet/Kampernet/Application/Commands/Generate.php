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

        $generator = new CodeGenerator(Yaml::parseFile(base_path('kampernet.yml')), base_path());
        try {
            $generator->writeBoilerPlate();
            $this->info("Successfully wrote boilerplate.");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

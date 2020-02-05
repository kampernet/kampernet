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

        $root = realpath(base_path());
        $app = Yaml::parseFile("$root/kampernet.yml");
        $generator = new CodeGenerator($app, $root);
        try {
            $generator->writeBoilerPlate();
            $this->info("Successfully wrote boilerplate. Next add \"VendorName\\\\ProjectName\\\\\": \"src/VendorName/ProjectName\" to your composer.json autoload psr-4 section.");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

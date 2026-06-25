<?php

namespace Nexphant\Console;

class SeedCommand extends Command
{
    protected string $name        = 'seed';
    protected string $description = 'Run database seeders';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $class  = $parsed['options']['class'] ?? null;
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir    = $base . '/database/seeders';

        if ($class !== null) {
            return $this->runClass($class);
        }

        // Auto-discover and run all seeders
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            require_once $file;
            $seederClass = 'Database\\Seeders\\' . basename($file, '.php');
            if (class_exists($seederClass)) {
                (new $seederClass())->run();
                $this->output("Seeded: {$seederClass}");
            }
        }

        return 0;
    }

    private function runClass(string $class): int
    {
        if (!class_exists($class)) {
            $this->error("Seeder class not found: {$class}");
            return 1;
        }
        (new $class())->run();
        $this->output("Seeded: {$class}");
        return 0;
    }
}

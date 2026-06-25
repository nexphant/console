<?php

namespace Nexphant\Console;

class FreshCommand extends Command
{
    protected string $name        = 'fresh';
    protected string $description = 'Drop all tables and re-run all migrations';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir    = $base . '/database/migrations';
        $seed   = isset($parsed['options']['seed']);

        if (!is_dir($dir)) {
            $this->error("Migration directory not found: {$dir}");
            return 1;
        }

        $runner = new \Nexphant\Database\MigrationRunner($dir);
        $runner->fresh();
        $this->output('Database wiped.');

        $ran = $runner->run();
        foreach ($ran as $file) {
            $this->output("Migrated: {$file}");
        }

        if ($seed) {
            return (new SeedCommand())->execute([]);
        }

        return 0;
    }
}

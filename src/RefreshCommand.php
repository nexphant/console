<?php

namespace Nexphant\Console;

class RefreshCommand extends Command
{
    protected string $name        = 'refresh';
    protected string $description = 'Rollback all migrations then re-run them';

    public function execute(array $args = []): int
    {
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir    = $base . '/database/migrations';

        if (!is_dir($dir)) {
            $this->error("Migration directory not found: {$dir}");
            return 1;
        }

        $runner = new \Nexphant\Database\MigrationRunner($dir);

        $rolledBack = $runner->rollbackAll();
        foreach ($rolledBack as $file) {
            $this->output("Rolled back: {$file}");
        }

        $ran = $runner->run();
        foreach ($ran as $file) {
            $this->output("Migrated: {$file}");
        }

        return 0;
    }
}

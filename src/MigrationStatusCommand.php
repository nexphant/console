<?php

namespace Nexphant\Console;

class MigrationStatusCommand extends Command
{
    protected string $name        = 'migrate:status';
    protected string $description = 'Show the status of each migration';

    public function execute(array $args = []): int
    {
        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/database/migrations';

        if (!is_dir($dir)) {
            $this->error("Migration directory not found: {$dir}");
            return 1;
        }

        $runner = new \Nexphant\Database\MigrationRunner($dir);
        $status = $runner->status();

        if (empty($status)) {
            $this->output('No migrations found.');
            return 0;
        }

        foreach ($status as $file => $ran) {
            $label = $ran ? '[✓]' : '[ ]';
            $this->output("{$label} {$file}");
        }

        return 0;
    }
}

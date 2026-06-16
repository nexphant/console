<?php

namespace nexphant\Console;

use nexphant\Runtime\AutoOptimize;

class OptimizeClearCommand extends Command
{
    protected string $name = 'optimize:clear';
    protected string $description = 'Clear compiled runtime artifacts';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $storagePath = (string) ($parsed['options']['storage'] ?? (getcwd() . '/storage'));

        (new AutoOptimize($storagePath))->clear();
        $this->output('Cleared.');

        return 0;
    }
}

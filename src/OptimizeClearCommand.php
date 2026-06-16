<?php

namespace Nexphant\Console;

use Nexphant\Runtime\AutoOptimize;

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

<?php

namespace Nexphant\Console;

use Nexphant\Runtime\OptimizeCompiler;
use Nexphant\Runtime\OptimizeManifest;

class OptimizeCommand extends Command
{
    protected string $name = 'optimize';
    protected string $description = 'Compile runtime hot path artifacts';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $storagePath = (string) ($parsed['options']['storage'] ?? (getcwd() . '/storage'));
        $compiler = new OptimizeCompiler($storagePath, getcwd());
        $files = $compiler->sourceFiles();

        $compiler->compile($files);
        (new OptimizeManifest($storagePath))->save($files);
        $this->output('Optimized.');

        return 0;
    }
}

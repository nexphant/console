<?php

namespace Nexphant\Console;

use Nexphant\Runtime\OptimizeLoader;

class PreloadCommand extends Command
{
    protected string $name = 'preload';
    protected string $description = 'Warm runtime classes for OPcache/APCu';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $storagePath = (string) ($parsed['options']['storage'] ?? (getcwd() . '/storage'));
        $file = $storagePath . '/nexphant/compiled/preload.php';
        $classes = is_file($file) ? (require $file) : [];
        $result = OptimizeLoader::warmup(is_array($classes) ? $classes : []);

        $this->output('Preloaded: ' . (int) $result['preloaded']);
        return 0;
    }
}

<?php

namespace Nexph\Console;

use Nexph\Runtime\OptimizeCompiler;
use Nexph\Runtime\OptimizeManifest;

class OptimizeCommand extends Command
{
    protected string $name = 'optimize';
    protected string $description = 'Compile runtime hot path artifacts';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $storagePath = (string) ($parsed['options']['storage'] ?? (getcwd() . '/storage'));
        $files = $this->getSourceFiles(getcwd());

        (new OptimizeCompiler($storagePath))->compile($files);
        (new OptimizeManifest($storagePath))->save($files);
        $this->output('Optimized.');

        return 0;
    }

    private function getSourceFiles(string $root): array
    {
        $files = [];
        foreach (['app.php', 'test.php', 'config/app.php', 'routes/web.php', 'routes/api.php'] as $file) {
            $path = $root . '/' . $file;
            if (is_file($path)) {
                $files[] = $path;
            }
        }
        return $files;
    }
}

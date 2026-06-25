<?php

/**
 * This file is part of the nexphant Framework.
 *
 * (c) nexphant <https://github.com/nexphant>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Nexphant\Console;

/**
 * cache:clear — clear all application caches
 */
class CacheClearCommand extends Command
{
    protected string $name        = 'cache:clear';
    protected string $description = 'Clear all application caches';

    public function execute(array $args = []): int
    {
        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dirs = [
            $base . '/storage/framework/cache',
            $base . '/storage/metadata',
        ];

        $total = 0;
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            foreach (glob($dir . '/*') ?: [] as $file) {
                if (is_file($file)) { unlink($file); $total++; }
            }
        }

        // Clear APCu if available
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }

        $this->output("Cache cleared. ({$total} files removed)");
        return 0;
    }
}

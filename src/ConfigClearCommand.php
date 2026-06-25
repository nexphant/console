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
 * config:clear — clear compiled/cached config
 */
class ConfigClearCommand extends Command
{
    protected string $name        = 'config:clear';
    protected string $description = 'Clear cached configuration';

    public function execute(array $args = []): int
    {
        $base  = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $files = [
            $base . '/storage/framework/config.php',
            $base . '/bootstrap/cache/config.php',
        ];

        $removed = 0;
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
                $removed++;
                $this->output("Removed: {$file}");
            }
        }

        if ($removed === 0) {
            $this->output('No cached config found.');
        } else {
            $this->output('Config cache cleared.');
        }

        return 0;
    }
}

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
 * serve — start the built-in PHP development server
 */
class ServeCommand extends Command
{
    protected string $name        = 'serve';
    protected string $description = 'Start the built-in PHP development server';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $host   = $parsed['options']['host'] ?? '127.0.0.1';
        $port   = $parsed['options']['port'] ?? '8000';
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $public = $parsed['options']['docroot'] ?? ($base . '/public');

        if (!is_dir($public)) {
            $public = $base;
        }

        $this->output("Starting server at http://{$host}:{$port}");
        $this->output("Document root: {$public}");
        $this->output("Press Ctrl+C to stop.");

        passthru(sprintf(
            'php -S %s:%s -t %s',
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($public)
        ));

        return 0;
    }
}

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
 * about — display framework and environment information
 */
class AboutCommand extends Command
{
    protected string $name        = 'about';
    protected string $description = 'Display information about the framework';

    public function execute(array $args = []): int
    {
        $base    = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $version = '1.0.0';

        $composer = $base . '/composer.json';
        if (file_exists($composer)) {
            $data    = json_decode(file_get_contents($composer), true);
            $version = $data['version'] ?? $version;
        }

        $this->output('Nexphant Framework');
        $this->output('Version  : ' . $version);
        $this->output('PHP      : ' . PHP_VERSION);
        $this->output('Base path: ' . $base);
        $this->output('ENV      : ' . ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'local'));
        $this->output('Debug    : ' . ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false'));

        return 0;
    }
}

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
 * make:service — scaffold a service class
 */
class MakeServiceCommand extends Command
{
    protected string $name        = 'make:service';
    protected string $description = 'Scaffold a new service class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:service <Name>');
            return 1;
        }

        $base  = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir   = $base . '/app/Services';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        file_put_contents($path, <<<PHP
<?php

namespace App\\Services;

class {$name}
{
    public function __construct()
    {
        //
    }
}
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

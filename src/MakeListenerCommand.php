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
 * make:listener — scaffold an event listener class
 */
class MakeListenerCommand extends Command
{
    protected string $name        = 'make:listener';
    protected string $description = 'Scaffold a new event listener class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:listener <Name> [--event=EventName]');
            return 1;
        }

        $event = $parsed['options']['event'] ?? 'SomeEvent';
        $base  = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir   = $base . '/app/Listeners';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        file_put_contents($path, <<<PHP
<?php

namespace App\\Listeners;

use App\\Events\\{$event};

class {$name}
{
    public function handle({$event} \$event): void
    {
        //
    }
}
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

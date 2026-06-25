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
 * make:job — scaffold a queue job class
 */
class MakeJobCommand extends Command
{
    protected string $name        = 'make:job';
    protected string $description = 'Scaffold a new queue job class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:job <Name>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/app/Jobs';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        file_put_contents($path, <<<PHP
<?php

namespace App\\Jobs;

class {$name}
{
    public function __construct(public readonly array \$payload = []) {}

    public function handle(): void
    {
        //
    }
}
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

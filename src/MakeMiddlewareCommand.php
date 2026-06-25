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
 * make:middleware — scaffold a new middleware class
 */
class MakeMiddlewareCommand extends Command
{
    protected string $name        = 'make:middleware';
    protected string $description = 'Scaffold a new middleware class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:middleware <Name>');
            return 1;
        }

        $base  = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir   = $base . '/app/Http/Middleware';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $class = basename(str_replace('\\', '/', $name));
        $path  = $dir . '/' . $class . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        file_put_contents($path, <<<PHP
<?php

namespace App\\Http\\Middleware;

use Nexphant\\Server\\ServerRequest;
use Nexphant\\Server\\ServerResponse;

class {$class}
{
    public function __invoke(ServerRequest \$request, ServerResponse \$response, callable \$next): mixed
    {
        return \$next(\$request, \$response);
    }
}
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

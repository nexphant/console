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

use Nexphant\Console\Command;

/**
 * make:controller — scaffold a controller class
 */
class MakeControllerCommand extends Command
{
    protected string $name        = 'make:controller';
    protected string $description = 'Scaffold a new controller class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:controller <Name>');
            return 1;
        }

        $path = $this->resolve($name, 'Http/Controllers', 'Controller');
        $this->writeFile($path, $this->stub($name));
        $this->output("Created: {$path}");
        return 0;
    }

    private function stub(string $name): string
    {
        $class = class_basename($name);
        $ns    = $this->namespace($name, 'App\\Http\\Controllers');
        return <<<PHP
<?php

namespace {$ns};

use Nexphant\\Server\\ServerRequest;
use Nexphant\\Server\\ServerResponse;

class {$class}
{
    public function index(ServerRequest \$request, ServerResponse \$response): mixed
    {
        return \$response->json(['message' => 'OK']);
    }
}
PHP;
    }

    private function resolve(string $name, string $subDir, string $suffix): string
    {
        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $rel  = str_replace('\\', '/', $name);
        if (!str_ends_with($rel, $suffix)) $rel .= $suffix;
        $dir  = $base . '/app/' . $subDir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir . '/' . basename($rel) . '.php';
    }

    private function namespace(string $name, string $base): string
    {
        $parts = explode('/', str_replace('\\', '/', $name));
        if (count($parts) > 1) {
            array_pop($parts);
            return $base . '\\' . implode('\\', $parts);
        }
        return $base;
    }

    private function writeFile(string $path, string $content): void
    {
        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return;
        }
        file_put_contents($path, $content);
    }
}

function class_basename(string $class): string
{
    $parts = explode('\\', str_replace('/', '\\', $class));
    return end($parts);
}

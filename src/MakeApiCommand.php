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
 * MakeApiCommand — generates a resource API controller scaffold.
 */
class MakeApiCommand extends Command
{
    protected string $name        = 'make:api';
    protected string $description = 'Generate a resource API controller scaffold';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['positional'][0] ?? null;

        if (!$name) {
            $this->error('Usage: make:api <ResourceName>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/app/Http/Controllers/Api';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $file = "{$dir}/{$name}Controller.php";
        if (file_exists($file)) {
            $this->error("Controller already exists: {$file}");
            return 1;
        }

        $lower = strtolower($name);
        $stub  = <<<PHP
<?php

namespace App\Http\Controllers\Api;

use Nexphant\Server\ServerRequest;
use Nexphant\Server\ServerResponse;

class {$name}Controller
{
    public function index(ServerRequest \$request): array
    {
        return [];
    }

    public function show(ServerRequest \$request, int \$id): array
    {
        return ['id' => \$id];
    }

    public function store(ServerRequest \$request): array
    {
        return \$request->all();
    }

    public function update(ServerRequest \$request, int \$id): array
    {
        return array_merge(['id' => \$id], \$request->all());
    }

    public function destroy(ServerRequest \$request, int \$id): array
    {
        return ['deleted' => \$id];
    }
}
PHP;

        file_put_contents($file, $stub);
        $this->output("Created: {$file}");
        return 0;
    }
}

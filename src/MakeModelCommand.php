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
 * make:model — scaffold a model class
 */
class MakeModelCommand extends Command
{
    protected string $name        = 'make:model';
    protected string $description = 'Scaffold a new model class';

    public function execute(array $args = []): int
    {
        $parsed    = $this->parseArgs($args);
        $name      = $parsed['arguments'][0] ?? null;
        $migration = isset($parsed['options']['migration']) || isset($parsed['options']['m']);

        if ($name === null) {
            $this->error('Usage: make:model <Name> [--migration]');
            return 1;
        }

        $base  = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir   = $base . '/app/Models';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';

        file_put_contents($path, <<<PHP
<?php

namespace App\\Models;

use Nexphant\\Database\\DB;

class {$name}
{
    protected static string \$table = '{$table}';

    public static function all(): array
    {
        return DB::table(static::\$table)->get();
    }

    public static function find(int|string \$id): ?array
    {
        return DB::table(static::\$table)->where('id', \$id)->first();
    }

    public static function create(array \$data): int|string
    {
        return DB::table(static::\$table)->insert(\$data);
    }

    public static function update(int|string \$id, array \$data): int
    {
        return DB::table(static::\$table)->where('id', \$id)->update(\$data);
    }

    public static function delete(int|string \$id): int
    {
        return DB::table(static::\$table)->where('id', \$id)->delete();
    }
}
PHP);

        $this->output("Created: {$path}");

        if ($migration) {
            $this->output("Hint: run make:migration create_{$table}_table to create the migration.");
        }

        return 0;
    }
}

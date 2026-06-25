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
 * make:migration — scaffold a database migration file
 */
class MakeMigrationCommand extends Command
{
    protected string $name        = 'make:migration';
    protected string $description = 'Scaffold a new database migration';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:migration <name>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/database/migrations';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $timestamp = date('Y_m_d_His');
        $filename  = $timestamp . '_' . $name . '.php';
        $class     = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $path      = $dir . '/' . $filename;

        file_put_contents($path, <<<PHP
<?php

use Nexphant\\Database\\Schema;

return new class {
    public function up(Schema \$schema): void
    {
        // \$schema->create('table_name', function (\$table) {
        //     \$table->id();
        //     \$table->timestamps();
        // });
    }

    public function down(Schema \$schema): void
    {
        // \$schema->drop('table_name');
    }
};
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

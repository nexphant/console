<?php

namespace Nexphant\Console;

class DiffCommand extends Command
{
    protected string $name        = 'diff';
    protected string $description = 'Generate a migration from model changes';

    public function execute(array $args = []): int
    {
        $parsed    = $this->parseArgs($args);
        $modelName = $parsed['arguments'][0] ?? null;

        if ($modelName === null) {
            $this->error('Usage: diff <ModelClass>');
            return 1;
        }

        if (!class_exists($modelName)) {
            $this->error("Class not found: {$modelName}");
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/database/migrations';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $table     = method_exists($modelName, 'getTable') ? $modelName::getTable() : strtolower(class_basename($modelName)) . 's';
        $timestamp = date('Y_m_d_His');
        $name      = "update_{$table}_table";
        $path      = $dir . "/{$timestamp}_{$name}.php";

        file_put_contents($path, <<<PHP
<?php
// Auto-generated diff migration for {$modelName}
use Nexphant\Database\Schema;

return new class {
    public function up(Schema \$schema): void
    {
        // TODO: apply model diff for table `{$table}`
    }

    public function down(Schema \$schema): void
    {
        // TODO: reverse diff for table `{$table}`
    }
};
PHP);

        $this->output("Created diff migration: {$path}");
        return 0;
    }
}

if (!function_exists('Nexphant\Console\class_basename')) {
    function class_basename(string $class): string
    {
        $parts = explode('\\', str_replace('/', '\\', $class));
        return end($parts);
    }
}

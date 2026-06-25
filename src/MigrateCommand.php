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
 * migrate — run pending database migrations
 */
class MigrateCommand extends Command
{
    protected string $name        = 'migrate';
    protected string $description = 'Run pending database migrations';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir    = $base . '/database/migrations';

        if (!is_dir($dir)) {
            $this->error("Migration directory not found: {$dir}");
            return 1;
        }

        $runner = new \Nexphant\Database\MigrationRunner($dir);
        $ran    = $runner->run();

        if (empty($ran)) {
            $this->output('Nothing to migrate.');
        } else {
            foreach ($ran as $file) {
                $this->output("Migrated: {$file}");
            }
        }

        return 0;
    }
}

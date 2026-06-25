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
 * rollback — rollback the last batch of migrations
 */
class RollbackCommand extends Command
{
    protected string $name        = 'rollback';
    protected string $description = 'Rollback the last batch of database migrations';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $steps  = (int) ($parsed['options']['step'] ?? 1);
        $base   = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir    = $base . '/database/migrations';

        $runner  = new \Nexphant\Database\MigrationRunner($dir);
        $rolled  = $runner->rollback($steps);

        if (empty($rolled)) {
            $this->output('Nothing to rollback.');
        } else {
            foreach ($rolled as $file) {
                $this->output("Rolled back: {$file}");
            }
        }

        return 0;
    }
}

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
 * MakeCrudCommand — generates a full CRUD scaffold (Model, Migration, Controller, Seeder).
 */
class MakeCrudCommand extends Command
{
    protected string $name        = 'make:crud';
    protected string $description = 'Generate a CRUD scaffold (Model, Migration, Controller, Seeder)';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['positional'][0] ?? null;

        if (!$name) {
            $this->error('Usage: make:crud <Name>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();

        // Model
        (new MakeModelCommand())->execute([$name]);

        // Migration
        (new MakeMigrationCommand())->execute(["create_" . strtolower($name) . "s_table"]);

        // Controller
        (new MakeControllerCommand())->execute(["{$name}Controller"]);

        // Seeder
        (new MakeSeederCommand())->execute(["{$name}Seeder"]);

        $this->output("CRUD scaffold for [{$name}] created.");
        return 0;
    }
}

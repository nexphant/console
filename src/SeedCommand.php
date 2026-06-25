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

use Nexphant\Database\DB;

/**
 * Upgraded SeedCommand — supports dependency graph, ordering, and transactions.
 */
class SeedCommand extends Command
{
    protected string $name        = 'seed';
    protected string $description = 'Run database seeders';

    public function execute(array $args = []): int
    {
        $parsed      = $this->parseArgs($args);
        $class       = $parsed['options']['class']       ?? null;
        $transaction = isset($parsed['flags']['transaction']) || isset($parsed['flags']['t']);
        $base        = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir         = $base . '/database/seeders';

        $run = function () use ($class, $dir): int {
            if ($class !== null) {
                return $this->runClass($class);
            }
            return $this->runAll($dir);
        };

        if ($transaction) {
            try {
                DB::beginTransaction();
                $result = $run();
                DB::commit();
                return $result;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Seeding failed (rolled back): ' . $e->getMessage());
                return 1;
            }
        }

        return $run();
    }

    // -------------------------------------------------------------------------

    private function runAll(string $dir): int
    {
        $files   = glob($dir . '/*.php') ?: [];
        $seeders = [];

        foreach ($files as $file) {
            require_once $file;
            $name  = basename($file, '.php');
            $fqcn  = 'Database\\Seeders\\' . $name;
            if (class_exists($fqcn)) {
                $seeders[$fqcn] = new $fqcn();
            }
        }

        $ordered = $this->resolveDependencyOrder($seeders);

        foreach ($ordered as $fqcn => $seeder) {
            $seeder->run();
            $this->output("Seeded: {$fqcn}");
        }

        return 0;
    }

    private function runClass(string $class): int
    {
        if (!class_exists($class)) {
            $this->error("Seeder class not found: {$class}");
            return 1;
        }
        $seeder = new $class();
        $seeder->run();
        $this->output("Seeded: {$class}");
        return 0;
    }

    /**
     * Resolve seeder dependency graph via topological sort.
     *
     * Seeders may declare a static $depends = [OtherSeeder::class] property.
     *
     * @param  array<string, object> $seeders
     * @return array<string, object>
     */
    private function resolveDependencyOrder(array $seeders): array
    {
        $visited = [];
        $ordered = [];

        $visit = function (string $class) use (&$visit, &$visited, &$ordered, $seeders): void {
            if (isset($visited[$class])) return;
            $visited[$class] = true;

            $deps = (new \ReflectionClass($class))->getStaticProperties()['depends'] ?? [];
            foreach ((array) $deps as $dep) {
                if (isset($seeders[$dep])) {
                    $visit($dep);
                }
            }

            if (isset($seeders[$class])) {
                $ordered[$class] = $seeders[$class];
            }
        };

        foreach (array_keys($seeders) as $class) {
            $visit($class);
        }

        return $ordered;
    }
}

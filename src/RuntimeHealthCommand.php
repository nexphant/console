<?php

/**
 * This file is part of the Nexph Framework.
 *
 * (c) Nexphlabs <https://github.com/nexphlabs>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Nexph\Console;

use Nexph\Runtime\Observability\HealthMonitor;
use Nexph\Runtime\Observability\RuntimeState;

class RuntimeHealthCommand extends Command {
    protected string $name = 'runtime:health';
    protected string $description = 'Check runtime health';

    public function execute(array $args = []): int {
        $parsed = $this->parseArgs($args);
        $json = isset($parsed['options']['json']);
        $health = (new HealthMonitor())->check();
        $state = RuntimeState::snapshot($parsed['options']['driver'] ?? null, $parsed['options']);
        $out = ['health' => $health, 'runtime' => $state['runtime'], 'server' => $state['server'], 'queue' => $state['queue'], 'system' => $state['system']];
        if ($json) {
            echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo sprintf("%s mode=%s server=%s workers=%d/%d queue=%d dlq=%d mem=%.1fMB\n", $health['state'], $state['runtime']['mode'], $state['server']['running'] ? 'running' : 'stopped', $state['server']['workers_reporting'], $state['server']['worker_count'], $state['queue']['depth'], $state['queue']['dead_letters'], $state['computed']['memory_usage_mb']);
        }
        return $health['state'] === 'unhealthy' ? 2 : 0;
    }
}

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

use Nexphant\Runtime\Observability\RuntimeState;

class RuntimeWorkersCommand extends Command
{
    protected string $name = 'runtime:workers';
    protected string $description = 'Display runtime worker lifecycle status';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $state = RuntimeState::snapshot($parsed['options']['driver'] ?? null, $parsed['options']);
        $server = $state['server'];
        if (isset($parsed['options']['json'])) {
            echo json_encode(['server' => $server, 'queue' => $state['queue'], 'runtime' => $state['runtime']], JSON_PRETTY_PRINT) . "\n";
            return 0;
        }
        echo sprintf(
            "http_workers=%d/%d pids=%s active_connections=%d active_requests=%d loop_timers=%d queue_workers=%d queue=%d running=%s\n",
            $server['workers_reporting'],
            $server['worker_count'],
            $server['pids'] === [] ? '-' : implode(',', $server['pids']),
            $server['active_connections'],
            $server['active_requests'],
            $server['loop']['timers'],
            $state['queue']['workers'],
            $state['queue']['depth'],
            $server['running'] ? 'yes' : 'no'
        );
        return 0;
    }
}

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

use Nexph\Runtime\Observability\RuntimeState;

/**
 * Runtime status command.
 */
class RuntimeStatusCommand extends Command {
    protected string $name = 'runtime:status';
    protected string $description = 'Display runtime status and health';
    
    public function execute(array $args = []): int {
        $parsed = $this->parseArgs($args);
        $options = $parsed['options'];
        
        $verbose = isset($options['verbose']) || isset($options['v']);
        $json = isset($options['json']);
        
        try {
            $state = RuntimeState::snapshot($options['driver'] ?? null, $options);
            $server = $state['server'];
            
            if ($json) {
                echo json_encode($state, JSON_PRETTY_PRINT) . "\n";
            } elseif ($verbose) {
                $this->output(sprintf('Runtime: %s pid=%s port=%s uptime=%s', $server['running'] ? 'running' : 'stopped', $server['pid'] ?? '-', $server['port'] ?? '-', gmdate('H:i:s', (int)$server['uptime'])));
                $this->output(sprintf('HTTP: workers=%d/%d requests=%d connections=%d active=%d', $server['workers_reporting'], $server['worker_count'], $server['total_requests'], $server['total_connections'], $server['active_connections']));
                $this->output(sprintf('Loop: lag=%.2fms timers=%d readers=%d writers=%d ticks=%d', $server['loop']['lag_ms'], $server['loop']['timers'], $server['loop']['readers'], $server['loop']['writers'], $server['loop']['ticks']));
                $this->output(sprintf('Queue: driver=%s running=%s workers=%d depth=%d dlq=%d', $state['queue']['driver'], $state['queue']['running'] ? 'yes' : 'no', $state['queue']['workers'], $state['queue']['depth'], $state['queue']['dead_letters']));
            } else {
                echo sprintf(
                    "%s | pid=%s port=%s http_workers=%d/%d requests=%d connections=%d queue=%d dlq=%d mem=%.1fMB cpu=%.2f\n",
                    $server['running'] ? 'running' : 'stopped',
                    $server['pid'] ?? '-',
                    $server['port'] ?? '-',
                    $server['workers_reporting'],
                    $server['worker_count'],
                    $server['total_requests'],
                    $server['active_connections'],
                    $state['queue']['depth'],
                    $state['queue']['dead_letters'],
                    $state['computed']['memory_usage_mb'],
                    (float)($state['system']['cpu_load'][0] ?? 0)
                );
            }
            
            return 0;
            
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}

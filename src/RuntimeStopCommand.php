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

/**
 * Runtime stop command.
 */
class RuntimeStopCommand extends Command
{
    protected string $name = 'runtime:stop';
    protected string $description = 'Stop runtime gracefully';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $options = $parsed['options'];

        $force = isset($options['force']) || isset($options['f']);

        try {
            $pidFile = getcwd() . '/storage/runtime.pid';
            $jsonFile = getcwd() . '/storage/runtime.json';
            $state = RuntimeState::serverSnapshot($options);
            $pidData = $this->readPidData($pidFile, $jsonFile);
            $pid = $pidData['pid'] ?: (int) ($state['pid'] ?? 0);
            $targets = $this->livePids(array_filter(array_merge([$pid], $pidData['children'], $state['pids'] ?? [])));

            if ($targets === []) {
                $this->error("Error: Runtime not running (no PID file)");
                $this->cleanupRuntimeFiles($pidFile, $jsonFile, (string) ($state['stats_dir'] ?? ''));
                return 1;
            }

            $signal = $force ? SIGKILL : SIGTERM;
            $signalName = $force ? 'SIGKILL' : 'SIGTERM';

            $this->output("Sending {$signalName} to process " . implode(',', $targets) . "...");

            if ($this->signalAll($targets, $signal)) {
                $this->output("Stop signal sent successfully.");

                if (!$force) {
                    $this->output("Waiting for graceful shutdown...");
                    $timeout = 30;
                    $start = time();

                    while ($this->livePids($targets) !== [] && (time() - $start) < $timeout) {
                        sleep(1);
                    }

                    if ($this->livePids($targets) !== []) {
                        $this->error("Warning: Process did not stop within {$timeout}s");
                        $this->error("Use --force to kill immediately");
                        return 1;
                    }
                }

                $this->cleanupRuntimeFiles($pidFile, $jsonFile, (string) ($state['stats_dir'] ?? ''));
                $this->output("Runtime stopped.");
                return 0;
            } else {
                $this->error("Error: Failed to send signal");
                return 1;
            }

        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function readPidData(string $pidFile, string $jsonFile): array
    {
        $data = ['pid' => 0, 'children' => []];
        if (file_exists($pidFile)) {
            $data['pid'] = (int) file_get_contents($pidFile);
        }
        if (file_exists($jsonFile)) {
            $json = json_decode((string) file_get_contents($jsonFile), true);
            if (is_array($json)) {
                $data['pid'] = (int) ($json['pid'] ?? $data['pid']);
                $data['children'] = array_map('intval', $json['children'] ?? []);
            }
        }
        return $data;
    }

    private function livePids(array $pids): array
    {
        $live = [];
        foreach (array_unique(array_map('intval', $pids)) as $pid) {
            if ($this->isLiveProcess($pid)) {
                $live[] = $pid;
            }
        }
        return $live;
    }

    private function signalAll(array $pids, int $signal): bool
    {
        $sent = false;
        foreach ($pids as $pid) {
            $sent = posix_kill((int) $pid, $signal) || $sent;
        }
        return $sent;
    }

    private function cleanupRuntimeFiles(string $pidFile, string $jsonFile, string $statsDir = ''): void
    {
        if (file_exists($pidFile)) {
            unlink($pidFile);
        }
        if (file_exists($jsonFile)) {
            unlink($jsonFile);
        }
        if ($statsDir !== '' && is_dir($statsDir)) {
            foreach (glob(rtrim($statsDir, '/') . '/worker-*.json') ?: [] as $file) {
                @unlink($file);
            }
        }
    }

    private function isLiveProcess(int $pid): bool
    {
        if ($pid <= 1 || !posix_kill($pid, 0)) {
            return false;
        }
        $statusFile = "/proc/{$pid}/status";
        if (is_readable($statusFile)) {
            $status = @file_get_contents($statusFile);
            if (is_string($status) && preg_match('/^State:\s+Z/m', $status)) {
                return false;
            }
        }
        return true;
    }
}

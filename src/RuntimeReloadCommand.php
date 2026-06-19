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
 * Runtime reload command.
 */
class RuntimeReloadCommand extends Command
{
    protected string $name = 'runtime:reload';
    protected string $description = 'Reload runtime configuration';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);

        try {
            $pidFile = getcwd() . '/storage/runtime.pid';
            $jsonFile = getcwd() . '/storage/runtime.json';
            $state = RuntimeState::serverSnapshot($parsed['options']);
            $pidData = $this->readPidData($pidFile, $jsonFile);
            $pid = $pidData['pid'] ?: (int) ($state['pid'] ?? 0);
            $targets = $this->livePids(array_filter(array_merge([$pid], $pidData['children'], $state['pids'] ?? [])));

            if ($targets === []) {
                $this->error("Error: Runtime not running (no PID file)");
                $this->cleanupRuntimeFiles($pidFile, $jsonFile);
                return 1;
            }

            $this->output("Stopping runtime " . implode(',', $targets) . "...");

            if (!$this->signalAll($targets, SIGKILL)) {
                $this->error("Error: Failed to stop runtime");
                return 1;
            }

            $deadline = microtime(true) + 3.0;
            while ($this->livePids($targets) !== [] && microtime(true) < $deadline) {
                usleep(200000);
            }
            $remaining = $this->livePids($targets);
            if ($remaining !== []) {
                $this->signalAll($remaining, SIGKILL);
            }

            $this->cleanupRuntimeFiles($pidFile, $jsonFile, (string) ($pidData['stats_dir'] ?? $state['stats_dir'] ?? ''));
            $this->output("Starting runtime...");
            return $this->runRuntime($pidData);
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function readPidData(string $pidFile, string $jsonFile): array
    {
        $data = ['pid' => 0, 'children' => [], 'command' => [], 'cwd' => getcwd(), 'php_binary' => PHP_BINARY, 'port' => null, 'host' => null, 'stats_dir' => ''];
        if (file_exists($pidFile)) {
            $data['pid'] = (int) file_get_contents($pidFile);
        }
        if (file_exists($jsonFile)) {
            $json = json_decode((string) file_get_contents($jsonFile), true);
            if (is_array($json)) {
                $data['pid'] = (int) ($json['pid'] ?? $data['pid']);
                $data['children'] = array_map('intval', $json['children'] ?? []);
                $data['command'] = is_array($json['command'] ?? null) ? $json['command'] : [];
                $data['cwd'] = (string) ($json['cwd'] ?? $data['cwd']);
                $data['php_binary'] = (string) ($json['php_binary'] ?? $data['php_binary']);
                $data['port'] = isset($json['port']) ? (int) $json['port'] : null;
                $data['host'] = isset($json['host']) ? (string) $json['host'] : null;
                $data['stats_dir'] = (string) ($json['stats_dir'] ?? '');
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

    private function runRuntime(array $pidData): int
    {
        $command = $pidData['command'] ?? [];
        if ($command === []) {
            return 1;
        }

        $script = (string) array_shift($command);
        $argv = array_merge([(string) ($pidData['php_binary'] ?? PHP_BINARY), $script], array_map('strval', $command));
        $cmd = implode(' ', array_map('escapeshellarg', $argv));
        $env = '';
        if (!empty($pidData['port'])) {
            $env .= 'PORT=' . escapeshellarg((string) $pidData['port']) . ' ';
        }
        if (!empty($pidData['host'])) {
            $env .= 'HOST=' . escapeshellarg((string) $pidData['host']) . ' ';
        }
        $cwd = (string) ($pidData['cwd'] ?? getcwd());
        $shell = 'cd ' . escapeshellarg($cwd) . ' && ' . $env . 'exec ' . $cmd;
        passthru($shell, $exitCode);
        return (int) $exitCode;
    }

    private function isLiveProcess(int $pid): bool
    {
        if ($pid <= 1 || $pid !== (int) $pid || !posix_kill($pid, 0)) {
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

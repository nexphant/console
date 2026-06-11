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

use Nexph\Queue\QueueFactory;
use Nexph\Runtime\Observability\RuntimeMetrics;
use Nexph\Runtime\Observability\HealthMonitor;
use Nexph\Runtime\Observability\Dashboard;
use Nexph\Runtime\Observability\Logger;

/**
 * Queue worker command.
 */
class QueueWorkCommand extends Command {
    protected string $name = 'queue:work';
    protected string $description = 'Start queue worker process';
    
    public function execute(array $args = []): int {
        $parsed = $this->parseArgs($args);
        $options = $parsed['options'];
        
        $driver = $options['driver'] ?? getenv('QUEUE_DRIVER') ?: 'file';
        $workers = (int)($options['workers'] ?? getenv('QUEUE_WORKERS') ?: 1);
        $maxIterations = (int)($options['max-iterations'] ?? 0);
        $sleep = (float)($options['sleep'] ?? 1.0);
        $dashboard = isset($options['dashboard']) || isset($options['d']);
        $verbose = isset($options['verbose']) || isset($options['v']);
        $quiet = isset($options['quiet']) || isset($options['q']);
        
        if (!$quiet) {
            $this->output("Starting queue worker...");
            $this->output("Driver: {$driver}");
            $this->output("Workers: {$workers}");
            $this->output("PHP: " . PHP_BINARY);
            if ($verbose) {
                $this->output("Mode: verbose");
            }
        }
        
        try {
            $queue = QueueFactory::create($driver, [
                'workers' => $workers,
                'poll_interval' => $sleep,
                'verbose' => $verbose,
                'quiet' => $quiet,
            ]);
            
            $metrics = new RuntimeMetrics();
            $health = new HealthMonitor();
            $dashboardObj = new Dashboard($metrics, $health);
            $logger = new Logger();
            
            $this->registerHandlers($queue, $quiet);
            
            if ($maxIterations > 0) {
                $this->runLimited($queue, $maxIterations, $sleep);
            } elseif ($dashboard) {
                $this->runWithDashboard($queue, $dashboardObj, $maxIterations);
            } else {
                $this->runSimple($queue, $maxIterations);
            }
            
            return 0;
            
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
    
    private function registerHandlers($queue, bool $quiet = false): void {
        $handlersDir = __DIR__ . '/../../app/Jobs';
        
        if (!is_dir($handlersDir)) {
            return;
        }
        
        foreach (glob($handlersDir . '/*.php') as $file) {
            require_once $file;
            
            $className = basename($file, '.php');
            $fullClassName = "App\\Jobs\\{$className}";
            
            if (class_exists($fullClassName)) {
                $jobName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
                $queue->register($jobName, $fullClassName);
                if (!$quiet) {
                    $this->output("Registered: {$jobName}");
                }
            }
        }
    }
    
    private function runSimple($queue, int $maxIterations): void {
        $this->output("Starting worker (blocking mode, pid: " . getmypid() . ")...");
        $queue->work();
    }

    private function runLimited($queue, int $maxIterations, float $sleep): void {
        $this->output("Starting worker (limited mode, pid: " . getmypid() . ")...");
        for ($i = 0; $i < $maxIterations; $i++) {
            if (!$queue->workOnce(1)) {
                usleep((int)($sleep * 1_000_000));
            }
        }
    }
    
    private function runWithDashboard($queue, Dashboard $dashboard, int $maxIterations): void {
        $this->output("Starting worker with dashboard (blocking mode)...");
        
        if (class_exists('\\Fiber')) {
            \Nexph\Runtime\Runtime::spawn(function() use ($dashboard) {
                while (true) {
                    echo "\033[2J\033[H";
                    echo $dashboard->render();
                    \Nexph\Runtime\Runtime::sleep(5);
                }
            });
        }

        $queue->work();
    }
}

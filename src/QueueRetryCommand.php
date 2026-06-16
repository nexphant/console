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

use Nexphant\Queue\QueueFactory;

/**
 * Queue retry command.
 */
class QueueRetryCommand extends Command
{
    protected string $name = 'queue:retry';
    protected string $description = 'Retry failed jobs';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $options = $parsed['options'];

        $driver = $options['driver'] ?? getenv('QUEUE_DRIVER') ?: 'file';
        $jobId = $parsed['arguments'][0] ?? null;
        $all = isset($options['all']);

        try {
            $queue = QueueFactory::create($driver);

            if ($all) {
                $retried = 0;
                foreach ($queue->failed(1000) as $job) {
                    if ($queue->retry($job->id)) {
                        $retried++;
                    }
                }
                $this->output("Retried {$retried} failed jobs.");
            } elseif ($jobId) {
                if (!$queue->retry($jobId)) {
                    $this->error("Error: Failed job {$jobId} not found");
                    return 1;
                }
                $this->output("Job {$jobId} queued for retry.");
            } else {
                $this->error("Error: Specify job ID or use --all");
                return 1;
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}

<?php

/**
 * This file is part of the Nexph Framework.
 *
 * (c) nexphant <https://github.com/nexphant>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Nexph\Console;

/**
 * Runtime CLI command system.
 * Provides commands for queue management, runtime control, and metrics.
 */
class CommandRegistry
{
    private array $commands = [];

    public function __construct()
    {
        $this->registerDefaultCommands();
    }

    /**
     * Register a command.
     */
    public function register(string $name, Command $command): void
    {
        $this->commands[$name] = $command;
    }

    /**
     * Get command by name.
     */
    public function get(string $name): ?Command
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Get all commands.
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Execute command.
     */
    public function execute(string $name, array $args = []): int
    {
        $command = $this->get($name);

        if ($command === null) {
            fwrite(STDERR, "Error: Unknown command '{$name}'\n");
            fwrite(STDERR, "Run 'nexph help' to see available commands\n");
            return 1;
        }

        try {
            return $command->execute($args);
        } catch (\Throwable $e) {
            fwrite(STDERR, "Error: {$e->getMessage()}\n");
            return 1;
        }
    }

    /**
     * Register default commands.
     */
    private function registerDefaultCommands(): void
    {
        $this->register('help', new HelpCommand($this));
        $this->register('optimize', new OptimizeCommand());
        $this->register('optimize:clear', new OptimizeClearCommand());
        $this->register('preload', new PreloadCommand());
        $this->register('queue:work', new QueueWorkCommand());
        $this->register('queue:stats', new QueueStatsCommand());
        $this->register('queue:clear', new QueueClearCommand());
        $this->register('queue:retry', new QueueRetryCommand());
        $this->register('runtime:status', new RuntimeStatusCommand());
        $this->register('runtime:metrics', new RuntimeMetricsCommand());
        $this->register('runtime:workers', new RuntimeWorkersCommand());
        $this->register('runtime:health', new RuntimeHealthCommand());
        $this->register('runtime:reload', new RuntimeReloadCommand());
        $this->register('runtime:stop', new RuntimeStopCommand());
        $this->register('runtime:doctor', new RuntimeDoctorCommand());
        $this->register('runtime:backends', new RuntimeBackendsCommand());
    }
}

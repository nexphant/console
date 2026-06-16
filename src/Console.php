<?php

namespace nexphant\Console;

class Console
{
    private CommandRegistry $registry;

    public function __construct()
    {
        $this->registry = new CommandRegistry();
    }

    public function run(array $argv): int
    {
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);
        return $this->registry->execute($command, $args);
    }

    public function register(string $name, Command $command): void
    {
        $this->registry->register($name, $command);
    }
}

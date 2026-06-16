<?php

namespace Nexphant\Console;

use Nexphant\Runtime\EventLoop\EventLoopFactory;
use Nexphant\Server\Socket\SocketDriverFactory;
use Nexphant\Server\Socket\AcceptStrategy;

class RuntimeBackendsCommand extends Command
{
    protected string $name = 'runtime:backends';
    protected string $description = 'Display selected runtime backends';

    public function execute(array $args = []): int
    {
        echo "\n";
        echo "nexphant Runtime Backends\n";
        echo str_repeat('=', 50) . "\n\n";

        $loop = EventLoopFactory::create();
        echo "Event Loop Backend: " . $this->getShortClassName($loop) . "\n";

        $socket = SocketDriverFactory::create();
        echo "Socket Driver: " . $this->getShortClassName($socket) . "\n";

        echo "Accept Strategy: " . AcceptStrategy::detect() . "\n";

        echo "\nEnvironment overrides:\n";
        echo "  NEXPHANT_LOOP=" . (getenv('NEXPHANT_LOOP') ?: 'auto') . "\n";
        echo "  NEXPHANT_SOCKET=" . (getenv('NEXPHANT_SOCKET') ?: 'auto') . "\n";

        echo "\n";
        return 0;
    }

    private function getShortClassName(object $obj): string
    {
        $class = get_class($obj);
        $parts = explode('\\', $class);
        return end($parts);
    }
}

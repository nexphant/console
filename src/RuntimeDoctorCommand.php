<?php

namespace Nexph\Console;

use Nexph\Support\Extension\ExtensionDetector;
use Nexph\Runtime\EventLoop\EventLoopFactory;

class RuntimeDoctorCommand extends Command
{
    protected string $name = 'runtime:doctor';
    protected string $description = 'Display runtime extension status and capabilities';

    public function execute(array $args = []): int
    {
        echo "\n";
        echo "Nexph Runtime Doctor\n";
        echo str_repeat('=', 50) . "\n\n";

        echo "Core:\n";
        $this->checkExtension('pcntl');
        $this->checkExtension('posix');
        $this->checkExtension('opcache');
        $this->checkExtension('json');
        $this->checkExtension('zlib');

        echo "\nNetwork:\n";
        $this->checkExtension('sockets');
        $this->checkExtension('event');
        $this->checkExtension('ev');
        $this->checkExtension('uv');

        echo "\nIPC:\n";
        $this->checkExtension('sysvsem');
        $this->checkExtension('sysvshm');
        $this->checkExtension('sysvmsg');
        $this->checkExtension('shmop');

        echo "\nCache:\n";
        $this->checkExtension('apcu');
        $this->checkExtension('redis');

        echo "\nExperimental:\n";
        $this->checkExtension('ffi');
        $this->checkExtension('parallel');

        echo "\nSelected drivers:\n";
        $loop = EventLoopFactory::create();
        echo "  Event Loop: " . $this->getShortClassName($loop) . "\n";

        $capabilities = ExtensionDetector::capabilities();
        echo "\nCapabilities:\n";
        foreach ($capabilities as $name => $enabled) {
            $status = $enabled ? '✓' : '✗';
            echo "  [$status] $name\n";
        }

        echo "\n";
        return 0;
    }

    private function checkExtension(string $name): void
    {
        $loaded = ExtensionDetector::has($name);
        $status = $loaded ? '[ok]' : '[missing]';
        $color = $loaded ? "\033[32m" : "\033[33m";
        $reset = "\033[0m";
        
        echo "  {$color}{$status}{$reset} {$name}\n";
    }

    private function getShortClassName(object $obj): string
    {
        $class = get_class($obj);
        $parts = explode('\\', $class);
        return end($parts);
    }
}

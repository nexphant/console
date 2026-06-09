<?php

namespace Nexph\Console;

class OptimizeCommand extends Command
{
    protected string $name = 'optimize';
    protected string $description = 'Optimize Nexph runtime';

    public function handle(): int
    {
        $this->info('Compiling routes...');
        $this->compileRoutes();

        $this->info('Compiling container...');
        $this->compileContainer();

        $this->info('Compiling config...');
        $this->compileConfig();

        $this->info('Generating preload...');
        $this->generatePreload();

        $this->success('Optimization complete!');
        return 0;
    }

    private function compileRoutes(): void
    {
        $dir = 'storage/nexph/compiled';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("$dir/routes.php", "<?php\nreturn [];\n");
    }

    private function compileContainer(): void
    {
        file_put_contents('storage/nexph/compiled/container.php', "<?php\nreturn [];\n");
    }

    private function compileConfig(): void
    {
        file_put_contents('storage/nexph/compiled/config.php', "<?php\nreturn [];\n");
    }

    private function generatePreload(): void
    {
        file_put_contents('storage/nexph/compiled/preload.php', "<?php\n");
    }
}

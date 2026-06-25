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

/**
 * make:data — scaffold a typed DTO class
 */
class MakeDataCommand extends Command
{
    protected string $name        = 'make:data';
    protected string $description = 'Scaffold a new typed DTO class';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['arguments'][0] ?? null;

        if ($name === null) {
            $this->error('Usage: make:data <Name>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/app/Data';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return 1;
        }

        file_put_contents($path, <<<PHP
<?php

namespace App\\Data;

/**
 * Typed DTO — no magic properties, explicit validation via attributes.
 */
class {$name}
{
    public function __construct(
        public readonly string \$id = '',
        // Add typed properties here
    ) {}

    public static function from(array \$data): self
    {
        return new self(
            id: \$data['id'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => \$this->id,
        ];
    }
}
PHP);

        $this->output("Created: {$path}");
        return 0;
    }
}

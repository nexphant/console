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
 * MakeFormCommand — generates a typed form class scaffold.
 */
class MakeFormCommand extends Command
{
    protected string $name        = 'make:form';
    protected string $description = 'Generate a form class scaffold';

    public function execute(array $args = []): int
    {
        $parsed = $this->parseArgs($args);
        $name   = $parsed['positional'][0] ?? null;

        if (!$name) {
            $this->error('Usage: make:form <FormName>');
            return 1;
        }

        $base = defined('NEXPHANT_BASE_PATH') ? NEXPHANT_BASE_PATH : getcwd();
        $dir  = $base . '/app/Forms';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $file = "{$dir}/{$name}.php";
        if (file_exists($file)) {
            $this->error("Form already exists: {$file}");
            return 1;
        }

        $stub = <<<PHP
<?php

namespace App\Forms;

use Nexphant\Foundation\Data;
use Nexphant\Validation\Rule;

class {$name} extends Data
{
    public string \$field = '';

    protected function rules(): array
    {
        return [
            'field' => Rule::string()->required(),
        ];
    }
}
PHP;

        file_put_contents($file, $stub);
        $this->output("Created: {$file}");
        return 0;
    }
}

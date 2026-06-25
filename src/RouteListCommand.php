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
 * route:list — display all registered routes
 */
class RouteListCommand extends Command
{
    protected string $name        = 'route:list';
    protected string $description = 'List all registered routes';

    private array $routes = [];

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public function execute(array $args = []): int
    {
        if (empty($this->routes)) {
            $this->output('No routes registered.');
            return 0;
        }

        $this->output(sprintf('%-8s %-40s %s', 'METHOD', 'URI', 'MIDDLEWARE'));
        $this->output(str_repeat('-', 80));

        foreach ($this->routes as $route) {
            $method  = strtoupper($route['method'] ?? 'GET');
            $uri     = $route['uri'] ?? '/';
            $mw      = implode(', ', $route['middleware'] ?? []);
            $this->output(sprintf('%-8s %-40s %s', $method, $uri, $mw));
        }

        return 0;
    }
}

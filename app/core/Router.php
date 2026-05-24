<?php

/**
 * Minimal front-controller router.
 *
 * Routes are registered as (method, path-pattern, handler). Patterns may
 * contain {name} placeholders that capture a URL segment and are passed to
 * the handler as arguments. Handlers are "Controller@method" strings.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, string $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    /**
     * Match the current request against the route table and dispatch it.
     * Falls back to a 404 page when nothing matches.
     */
    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = $this->compile($routePath);
            if (preg_match($regex, $path, $matches)) {
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );
                $this->call($handler, array_values($params));
                return;
            }
        }

        $this->notFound();
    }

    /** Turn "/switches/{slug}" into a named-capture regex. */
    private function compile(string $path): string
    {
        $pattern = preg_replace('#\{([a-z_]+)\}#', '(?<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function call(string $handler, array $params): void
    {
        [$controller, $action] = explode('@', $handler);
        (new $controller())->$action(...$params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        view('errors/404');
    }
}

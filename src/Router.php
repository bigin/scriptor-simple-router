<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter;

final class Router
{
    private static ?self $instance = null;

    /** @var list<Route> */
    private array $routes = [];

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function get(string $pattern, mixed $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, mixed $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, mixed $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    private function add(string $method, string $pattern, mixed $handler): void
    {
        [$regex, $paramNames] = self::compilePattern($pattern);
        $this->routes[] = new Route($method, $pattern, $handler, $regex, $paramNames);
    }

	/**
     * Compile a route pattern like '/api/users/{id}' into a regex
     * and capture the parameter names.
     *
     * @return array{0: string, 1: list<string>} [regex, paramNames]
     */
    private static function compilePattern(string $pattern): array
    {
        $paramNames = [];
        $regex = preg_replace_callback(
            '#\{([A-Za-z_][A-Za-z0-9_]*)\}#',
            static function (array $m) use (&$paramNames): string {
                $paramNames[] = $m[1];
                return '(?P<' . $m[1] . '>[^/]+)';
            },
            $pattern,
        );

        // Anchor and escape the static parts. `preg_quote` would
        // also escape the regex we just inserted, so we anchor
        // around the already-substituted string instead.
        return ['#^' . $regex . '$#', $paramNames];
    }

	/**
     * Match a request against the registered routes.
     *
     * @return array{route: Route, params: array<string, string>}|null
     *         The first matching route plus its extracted path
     *         params, or null when nothing matches.
     */
    public function match(string $path, string $method): ?array
    {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }
            if (preg_match($route->regex, $path, $m) !== 1) {
                continue;
            }

            $params = [];
            foreach ($route->paramNames as $name) {
                $params[$name] = $m[$name];
            }
            return ['route' => $route, 'params' => $params];
        }
        return null;
    }

	/**
     * Match the request against registered routes and invoke the
     * matching handler. Returns the handler's Response, or null if
     * no route matched (the caller decides what to do then; usually
     * fall through to the next handler in the pipeline).
     */
    public function dispatch(Request $request): ?Response
    {
        $hit = $this->match($request->path, $request->method);
        if ($hit === null) {
            return null;
        }

        $request = $request->withPathParams($hit['params']);
        $response = self::invoke($hit['route']->handler, $request);

        if (! $response instanceof Response) {
            throw new \LogicException(sprintf(
                'Handler for %s %s must return a %s, got %s.',
                $hit['route']->method,
                $hit['route']->pattern,
                Response::class,
                get_debug_type($response),
            ));
        }

        return $response;
    }

    /**
     * Resolve any of the three supported handler shapes into a call
     * with the Request as its only argument.
     *
     *   - Closure                 ->  $handler($request)
     *   - 'App\Controller'        ->  (new App\Controller())($request)         // __invoke
     *   - ['App\Controller', 'm'] ->  (new App\Controller())->m($request)
     */
    private static function invoke(mixed $handler, Request $request): mixed
    {
        if ($handler instanceof \Closure) {
            return $handler($request);
        }
        if (\is_string($handler) && class_exists($handler)) {
            return (new $handler())($request);
        }
        if (\is_array($handler) && \count($handler) === 2 && \is_string($handler[0]) && \is_string($handler[1])) {
            [$class, $method] = $handler;
            return (new $class())->$method($request);
        }
        throw new \LogicException('Unsupported handler shape: ' . get_debug_type($handler));
    }

	/**
     * One-call bridge from the live request to a sent Response.
     * Returns true when a route matched (and the Response was
     * sent), false when nothing matched (and the caller should
     * continue the normal pipeline).
     *
     * Used from a theme's `_ext.php` as:
     *
     *     if (\Bigins\ScriptorSimpleRouter\Router::handle()) return;
     */
    public static function handle(): bool
    {
        $request  = Request::fromGlobals();
        $response = self::instance()->dispatch($request);
        if ($response === null) {
            return false;
        }
        $response->send();
        return true;
    }
}
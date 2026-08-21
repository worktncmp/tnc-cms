<?php

declare(strict_types=1);

namespace Core;

final class Router
{
    /** @var list<array{method: string, path: string, regex: string, names: list<string>, handler: array{0: class-string, 1: string}, name: ?string}> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $named = [];

    public function __construct(private readonly Application $app)
    {
    }

    public function get(string $path, array $handler, ?string $name = null): void
    {
        $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, array $handler, ?string $name = null): void
    {
        $this->add('POST', $path, $handler, $name);
    }

    public function put(string $path, array $handler, ?string $name = null): void
    {
        $this->add('PUT', $path, $handler, $name);
    }

    public function patch(string $path, array $handler, ?string $name = null): void
    {
        $this->add('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, array $handler, ?string $name = null): void
    {
        $this->add('DELETE', $path, $handler, $name);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function add(string $method, string $path, array $handler, ?string $name = null): void
    {
        [$regex, $names] = $this->compile($path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'names' => $names,
            'handler' => $handler,
            'name' => $name,
        ];

        if ($name !== null) {
            $this->named[$name] = $path;
        }
    }

    public function dispatch(Request $request): mixed
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }

            $pathMatched = true;
            if ($route['method'] !== $request->method) {
                continue;
            }

            $params = [];
            foreach ($route['names'] as $index => $paramName) {
                $params[$paramName] = $matches[$index + 1] ?? '';
            }
            $request->params = $params;

            return $this->runHandler($route['handler'], $params);
        }

        if ($request->method === 'GET') {
            $page = $this->app->pages()->resolve($request->path);
            if ($page !== null) {
                return $this->app->renderPage($page);
            }
        }

        if ($pathMatched || $this->app->pages()->resolve($request->path) !== null) {
            throw HttpException::methodNotAllowed();
        }

        throw HttpException::notFound();
    }

    /** @param array<string, string> $params */
    public function url(string $name, array $params = []): string
    {
        $path = $this->named[$name] ?? null;
        if ($path === null) {
            throw new \InvalidArgumentException('Unknown route: ' . $name);
        }

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', rawurlencode((string) $value), $path);
        }

        if (str_contains($path, '{')) {
            throw new \InvalidArgumentException('Missing parameters for route: ' . $name);
        }

        return $path;
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param array<string, string> $params
     */
    private function runHandler(array $handler, array $params): mixed
    {
        [$class, $method] = $handler;
        if (!class_exists($class) || !method_exists($class, $method)) {
            throw new \RuntimeException('Route handler not found.');
        }

        $controller = new $class($this->app);
        $arguments = array_values($params);

        return $controller->{$method}(...$arguments);
    }

    /** @return array{0: string, 1: list<string>} */
    private function compile(string $path): array
    {
        $names = [];
        $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $path, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regex = '';

        foreach ($parts ?: [] as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $match)) {
                $names[] = $match[1];
                $regex .= '([^/]+)';
            } else {
                $regex .= preg_quote($part, '#');
            }
        }

        return ['#^' . $regex . '$#', $names];
    }
}

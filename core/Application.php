<?php

declare(strict_types=1);

namespace Core;

final class Application
{
    private static ?self $instance = null;

    private Config $config;
    private ?Request $request = null;
    private ?Database $database = null;
    private ?Session $session = null;
    private ?View $view = null;
    private ?Router $router = null;
    private ?PageResolver $pages = null;
    private ?Auth $auth = null;
    private ?Csrf $csrf = null;
    private ?Logger $logger = null;
    private ?Upload $upload = null;

    /** @var array<string, mixed> */
    private array $memorySession = [];

    public function __construct(private readonly string $basePath)
    {
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been bootstrapped.');
        }

        return self::$instance;
    }

    public static function setInstance(?self $application): void
    {
        self::$instance = $application;
    }

    public function boot(): self
    {
        Env::load($this->basePath('.env'));
        $this->config = Config::load($this->basePath('config'));
        date_default_timezone_set((string) $this->config->get('app.timezone', 'UTC'));

        $this->logger = new Logger($this->basePath('storage/logs'));
        $this->view = new View($this->basePath('app/Views'), $this->basePath());
        $this->pages = new PageResolver(
            $this->basePath('content/pages'),
            $this->basePath('app/Views/layouts'),
            $this->basePath('storage/cache'),
            $this->isDebug(),
        );
        $this->csrf = new Csrf();
        $this->router = new Router($this);

        $routes = $this->basePath('app/routes.php');
        if (is_file($routes)) {
            $router = $this->router;
            require $routes;
        }

        $this->registerErrorHandlers();

        return $this;
    }

    public function run(): void
    {
        $request = Request::capture();
        $this->session = Session::start([
            'name' => (string) $this->config->get('session.name', 'cms_session'),
            'lifetime' => (int) $this->config->get('session.lifetime', 0),
            'path' => $this->basePath('storage/sessions'),
        ], $request->isHttps());

        try {
            $this->handle($request)->send();
        } catch (\Throwable $exception) {
            $this->logger()->error($exception);
            $message = $this->isDebug() ? $exception->getMessage() : 'An unexpected error occurred.';
            $this->applySecurityHeaders(
                $this->httpError(new HttpException(500, $message, $exception)),
            )->send();
        }
    }

    public function handle(Request $request): Response
    {
        $this->request = $request;
        $this->session ??= Session::fake($this->memorySession);

        try {
            if ($request->trailingSlash && $request->path !== '/') {
                return $this->applySecurityHeaders(Response::redirect(url($request->path), 301));
            }

            if ($this->isUnsafe($request->method)) {
                $this->csrf()->verify($request, $this->session());
            }

            $result = $this->router()->dispatch($request);

            return $this->applySecurityHeaders($this->normalize($result));
        } catch (HttpException $exception) {
            return $this->applySecurityHeaders($this->httpError($exception));
        }
    }

    public function withSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function basePath(string $append = ''): string
    {
        return Path::join($this->basePath, $append);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }

    public function request(): Request
    {
        if ($this->request === null) {
            throw new \RuntimeException('No request is active.');
        }

        return $this->request;
    }

    public function session(): Session
    {
        return $this->session ??= Session::fake($this->memorySession);
    }

    public function view(): View
    {
        if ($this->view === null) {
            throw new \RuntimeException('View renderer is not ready.');
        }

        return $this->view;
    }

    public function router(): Router
    {
        if ($this->router === null) {
            throw new \RuntimeException('Router is not ready.');
        }

        return $this->router;
    }

    public function pages(): PageResolver
    {
        if ($this->pages === null) {
            throw new \RuntimeException('Page resolver is not ready.');
        }

        return $this->pages;
    }

    public function db(): Database
    {
        return $this->database ??= new Database((array) $this->config->get('database', []));
    }

    public function auth(): Auth
    {
        return $this->auth ??= new Auth($this);
    }

    public function csrf(): Csrf
    {
        return $this->csrf ??= new Csrf();
    }

    public function logger(): Logger
    {
        return $this->logger ??= new Logger($this->basePath('storage/logs'));
    }

    public function upload(): Upload
    {
        return $this->upload ??= new Upload();
    }

    public function isDebug(): bool
    {
        return (bool) $this->config->get('app.debug', false);
    }

    /**
     * @param array{contentFile: string, type: string, directory: string, meta: array<string, mixed>} $page
     */
    public function renderPage(array $page): Response
    {
        $meta = $page['meta'];
        $data = $this->sharedViewData();

        if ($page['type'] === 'html') {
            $html = (string) file_get_contents($page['contentFile']);
        } else {
            $included = $this->view()->includeFile(
                $page['contentFile'],
                $data,
                [$this->basePath('content/pages')],
            );
            $html = $included['html'];
            if (isset($included['vars']['page']) && is_array($included['vars']['page'])) {
                $meta = array_merge($meta, $included['vars']['page']);
            }
        }

        $data['title'] = is_string($meta['title'] ?? null)
            ? $meta['title']
            : $this->titleFromPath($this->request()->path);
        $data['page'] = $meta;

        $layoutFile = $this->pages()->resolveLayout($page['directory'], $meta);
        $body = $this->view()->layout($html, $layoutFile, $data, [
            $this->basePath('app/Views'),
            $this->basePath('content/pages'),
        ]);

        return Response::html($body);
    }

    /** @return array<string, mixed> */
    public function sharedViewData(): array
    {
        return [
            'appName' => $this->config('app.name'),
            'title' => $this->config('app.name'),
            'flashSuccess' => $this->session()->getFlash('success'),
            'flashError' => $this->session()->getFlash('error'),
            'currentUser' => $this->auth()->user(),
            'currentPath' => $this->request()->path,
        ];
    }

    private function normalize(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if (is_string($result)) {
            return Response::html($result);
        }

        throw new \RuntimeException('Invalid route result.');
    }

    private function httpError(HttpException $exception): Response
    {
        $status = $exception->status;

        if (
            $status === 403
            && $this->request !== null
            && !$this->auth()->check()
            && str_starts_with($this->request->path, '/admin')
        ) {
            $intended = $this->request->path;
            $query = $this->request->query;
            if ($query !== []) {
                $intended .= '?' . http_build_query($query);
            }
            $safe = safe_internal_path($intended);
            if ($safe !== null) {
                $this->session()->set('intended_url', $safe);
            }
        }

        $view = match ($status) {
            404 => 'errors/404',
            403 => 'errors/403',
            405 => 'errors/405',
            default => 'errors/500',
        };

        $data = $this->request !== null ? $this->sharedViewData() : [
            'appName' => $this->config('app.name'),
            'title' => (string) $status,
            'flashSuccess' => null,
            'flashError' => null,
            'currentUser' => null,
            'currentPath' => '/',
        ];
        $data['title'] = (string) $status;
        $data['message'] = $this->isDebug() || $status < 500 ? $exception->getMessage() : 'An unexpected error occurred.';
        $data['debug'] = $this->isDebug();
        $data['trace'] = $this->isDebug() ? $exception->getTraceAsString() : '';
        $data['showLogin'] = $status === 403 && ($this->auth()->user() === null);

        try {
            $html = $this->view()->render($view, $data);
            $layoutFile = $this->basePath('app/Views/layouts/default.php');
            if (is_file($layoutFile)) {
                $html = $this->view()->layout($html, $layoutFile, $data);
            }

            return Response::html($html, $status);
        } catch (\Throwable) {
            $safe = htmlspecialchars($data['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return Response::html('<h1>' . $status . '</h1><p>' . $safe . '</p>', $status);
        }
    }

    private function applySecurityHeaders(Response $response): Response
    {
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->header('X-XSS-Protection', '0');

        return $response;
    }

    private function isUnsafe(string $method): bool
    {
        return in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function titleFromPath(string $path): string
    {
        if ($path === '/') {
            return (string) $this->config('app.name');
        }

        return ucwords(str_replace(['-', '_'], ' ', basename($path)));
    }

    private function registerErrorHandlers(): void
    {
        $debug = $this->isDebug();
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        if (PHP_SAPI === 'cli') {
            return;
        }

        set_exception_handler(function (\Throwable $exception): void {
            $this->logger()->error($exception);
            $message = $this->isDebug() ? $exception->getMessage() : 'An unexpected error occurred.';
            $this->applySecurityHeaders(
                $this->httpError($exception instanceof HttpException ? $exception : new HttpException(500, $message, $exception)),
            )->send();
        });
    }
}

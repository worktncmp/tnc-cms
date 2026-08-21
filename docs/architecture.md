# Architecture

TNC-CMS is a small, dependency-free PHP website kernel.

## Goals

- Fast for brochure sites (folder → URL)
- Clear for forms and database work (controllers + routes)
- Secure by default
- Small enough that one developer can understand the whole codebase

## Not included (on purpose)

No Composer packages, ORM, service container, facades, queues, events, template compiler, plugin system, or admin dashboard.

## Major areas

```text
public/     web root
app/        this website’s PHP and views
content/    convention pages
core/       framework
config/     configuration
storage/    logs, cache, sessions
bootstrap/  startup
```

## Lifecycle

```text
HTTP → public/index.php → Autoloader → .env + config → Application
  → Request → Session → CSRF (unsafe methods)
  → Router (explicit, then convention page, then 404)
  → Controller or page view → Layout → Response
```

## Routing

1. Explicit routes in `app/routes.php` win when method and path match.
2. Otherwise GET requests resolve under `content/pages`.
3. Otherwise 404 (or 405 if the path exists for another method).

## Layouts

Nearest `layout.php` under `content/pages`, else `app/Views/layouts/default.php`.  
Named layouts from page metadata override that. One layout per request.

## MVC

| Layer | Responsibility |
|---|---|
| Router / PageResolver | Match URL |
| Controller | HTTP in, call model, return view/redirect |
| Model | SQL for one table/concept |
| View | Presentation only |
| Database | PDO wrapper |

## Security boundaries

- Includes must stay inside allowed roots (`realpath` checks)
- Prepared statements only
- CSRF on unsafe methods
- Production errors do not expose internals

## Production page cache

With `APP_DEBUG=false`, page routes are read from `storage/cache/pages.php`.  
Rebuild with `php scripts/cache-pages.php`.

# Architecture

TNC-CMS is a small, dependency-free PHP website kernel with a built-in admin area for editors.

Public pages stay file-first (folder → URL). Signed-in users can manage HTML pages, media, messages, and (with the right role) products and users — without Composer, npm, or a third-party framework.

## Goals

- Fast for brochure sites (folder → URL)
- Editable through admin for common content work (pages, images, inbox)
- Clear for forms and database work (controllers + routes)
- Secure by default
- Small enough that one developer can understand the whole codebase

## Included

| Area | What it does |
|---|---|
| **Convention routing** | `content/pages/.../index.php\|html` → URLs automatically |
| **Explicit routes** | Controllers for forms, lists, and admin |
| **Admin area** | Dashboard at `/admin` for signed-in users |
| **Content pages admin** | Create, edit, delete HTML pages under `content/pages` |
| **Media library** | Upload images to `public/uploads` for use in pages |
| **Auth & roles** | `admin` and `editor` with permission checks |
| **MVC helpers** | Controllers, models, views, PDO wrapper |
| **CSRF, sessions, uploads** | Built into `core/` |

## Not included (on purpose)

No Composer packages, ORM, service container, facades, queues, events, template compiler, plugin system, block editor (Gutenberg), revision history, or multi-tenant hosting.

The admin is intentionally small: not WordPress, not Laravel Nova.

## Major areas

```text
public/       web root (assets, uploads)
app/          this website’s PHP, views, and services
  Controllers/        public + admin HTTP handlers
  Controllers/Admin/  protected admin screens
  Services/           page and media file logic
  Models/             database queries
  Views/              layouts, partials, admin templates
content/      convention pages (also edited via admin)
core/         framework (router, auth, DB, views)
config/       configuration
storage/      logs, cache, sessions
bootstrap/    startup
scripts/      migrate, cache, user tools
tests/        built-in test runner
```

## Lifecycle

```text
HTTP → public/index.php → Autoloader → .env + config → Application
  → Request → Session → CSRF (unsafe methods)
  → Router (explicit, then convention page, then 404)
  → Controller or page view → Layout → Response
```

Admin requests follow the same pipeline. Admin controllers extend a base that requires sign-in and checks permissions before running.

## Routing

1. Explicit routes in `app/routes.php` win when method and path match.
2. Otherwise GET requests resolve under `content/pages`.
3. Otherwise 404 (or 405 if the path exists for another method).

Admin URLs (`/admin`, `/admin/pages`, `/login`, etc.) are explicit routes, not convention pages.

## Content pages (public + admin)

Convention pages live under `content/pages`. Each page folder typically has:

| File | Role |
|---|---|
| `index.html` | Editable body (safe for admin WYSIWYG) |
| `index.php` | Developer template (body not editable in admin unless converted) |
| `page.json` | Title and optional metadata |
| `layout.php` | Section layout (optional, nearest wins) |

**Public site:** `PageResolver` discovers pages and serves them through layouts.

**Admin:** `ContentPageService` reads and writes HTML pages on disk, sanitizes saved HTML, and can convert between `index.php` and `index.html` in either direction (admin role only).

After page changes in production, rebuild the route cache: `php scripts/cache-pages.php`.

## Admin architecture

```text
/login → AuthController
/admin/* → AdminController subclasses
  → requirePermission(...) via core/Auth.php
  → Services (ContentPageService, MediaService) or Models
  → admin layout view
```

| Section | Service / model | Roles |
|---|---|---|
| Dashboard | counts from DB + filesystem | admin, editor |
| Pages | `ContentPageService` | admin, editor |
| Media | `MediaService` + `Upload` | admin, editor |
| Messages | `Message` model | admin, editor |
| Products | `Product` model | admin only |
| Users | `User` model | admin only |

The page editor loads TinyMCE from a CDN on the edit screen only. Public visitors never load admin JavaScript.

Permissions are string keys (`pages.manage`, `media.view`, …) mapped to roles in `core/Auth.php`.

## Layouts

Nearest `layout.php` under `content/pages`, else `app/Views/layouts/default.php`.  
Named layouts from page metadata override that. One layout per request.

Admin uses `app/Views/layouts/admin.php` — separate from the public site layout.

## MVC

| Layer | Responsibility |
|---|---|
| Router / PageResolver | Match URL |
| Controller | HTTP in, call service/model, return view/redirect |
| Service | File-based domain logic (pages, media) |
| Model | SQL for one table/concept |
| View | Presentation only |
| Database | PDO wrapper |

## Security boundaries

- Includes must stay inside allowed roots (`realpath` checks)
- Prepared statements only
- CSRF on unsafe methods
- Admin actions require authentication and role permissions
- HTML saved from the page editor is sanitized (scripts and inline handlers stripped)
- Production errors do not expose internals

## Production page cache

With `APP_DEBUG=false`, page routes are read from `storage/cache/pages.php`.  
Rebuild with `php scripts/cache-pages.php` after adding, renaming, or removing page folders outside admin, or after bulk deploys.

Admin create/edit/delete clears this cache automatically.

## Lightweight by design

The codebase stays small: no vendor folder, no build step, ~two service classes for admin file work, and heavy UI (TinyMCE) loaded only when an editor opens the page form. Feature growth should stay in `app/` and optional routes — not in a plugin system or package stack.

See also [Admin](admin.md) and [For developers](for-developers.md).

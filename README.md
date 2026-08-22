# TNC-CMS

TNC-CMS is a small, dependency-free PHP website CMS. Copy this project, configure it, add pages as folders or through the admin area, and use controllers only when you need forms or a database.

- PHP 8.2+
- Web root is always `public/`
- No Composer or npm required

---

## Start here

| Who you are | Read this |
|---|---|
| Anyone setting up the project | [Getting started](docs/getting-started.md) |
| Editing text and pages (little or no PHP) | [For editors](docs/for-editors.md) |
| Writing PHP, routes, database code | [For developers](docs/for-developers.md) |
| “Where do I put X?” | [Folder guide](docs/folder-guide.md) |
| Going live safely | [Security](docs/security.md) |
| Using the admin area | [Admin](docs/admin.md) |
| How the system is designed | [Architecture](docs/architecture.md) |

---

## Quick start

```text
php scripts/migrate.php
php -S 127.0.0.1:8080 -t public public/router.php
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080).

Sample login (admin):

- Email: `editor@example.com`
- Password: `TNC-demo-1`

Sample login (writer):

- Email: `writer@example.com`
- Password: `a-strong-password`

---

## Sample pages included

| URL | Shows |
|---|---|
| `/` | Home |
| `/about` | Simple content page |
| `/services` | HTML page + section layout |
| `/services/web-development` | Nested page inheriting that layout |
| `/contact` | Form (POST handled by a controller) |
| `/products` | Database list via controller |
| `/admin` | Protected admin (after sign in) |

Sample login opens the **admin area** — edit HTML pages, upload media, read contact messages, and (as admin) manage products and users.

---

## Core idea

1. Normal pages live in `content/pages`. Folder name = URL.
2. Editors can create and edit HTML pages at `/admin/pages` (saved as `index.html` on disk).
3. Header, footer, and menu live in `app/Views/partials`.
4. Forms and database pages use `app/Controllers` and `app/routes.php`.
5. Do not edit `core/` for a normal website.

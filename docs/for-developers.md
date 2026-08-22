# For developers

TNC-CMS is PHP 8.2+, no Composer packages, no third-party framework. It combines convention-based public pages with an admin area for editors (`/admin`).

- Website code: `app/`
- Content pages: `content/pages/` (also writable from admin)
- Engine: `core/` (leave alone for normal sites)

---

## Request flow

```text
Browser
  → public/index.php
  → bootstrap + config
  → Application
  → Router
       1. Explicit route in app/routes.php (method + path)
       2. Else GET convention page in content/pages
       3. Else 404
  → Layout wraps the content
  → Response
```

Document root must be `public/`.

---

## Auto routing (convention pages)

GET pages need no entry in `routes.php`:

```text
content/pages/index.php                         → /
content/pages/about/index.php                   → /about
content/pages/services/web-development/index.php → /services/web-development
```

Rules:

- Only `index.php` or `index.html` become routes
- `layout.php` and `page.json` are never routes
- Convention pages are GET only
- Path traversal is rejected

`index.html` is read as text (PHP is not executed).  
`index.php` is included as a view. Keep SQL and heavy logic out of page files.

### Page metadata

```php
$page = [
    'title' => 'About',
    'layout' => 'default', // optional named layout in app/Views/layouts
];
```

Or use `page.json` next to the page.

### Layout resolution (nearest wins)

1. Named layout from metadata, else  
2. Nearest `layout.php` walking up from the page folder, else  
3. `app/Views/layouts/default.php`

One layout per request (not nested wrapping).

---

## Explicit routes

File: `app/routes.php`

```php
$router->get('/products/{id}', [ProductController::class, 'show'], 'product.show');
$router->post('/contact', [ContactController::class, 'submit']);
```

Methods: `get`, `post`, `put`, `patch`, `delete`.

`{id}` is one URL segment.

A POST-only route does not hide a GET content page at the same path.

Named URL helper:

```php
route('product.show', ['id' => $id]);
```

---

## Controllers, models, views

```text
app/Controllers/ProductController.php
app/Models/Product.php
app/Views/products/show.php
```

Controllers stay thin. Models use `Core\Database` (PDO, arrays, no ORM).

```php
return $this->view('products/show', [
    'title' => $product['title'],
    'product' => $product,
]);
```

Helpers:

```php
e($value);
url('/about');
asset('css/app.css');
route('product.show', ['id' => $id]);
partial('header', [...]);
component('card', [...]);
csrf_field();
old('email');
```

Always escape untrusted output with `e()`.

---

## Database

`.env`: `DB_DRIVER=sqlite` or `mysql`.

```text
php scripts/migrate.php
php scripts/create-user.php you@example.com a-strong-password "Your Name"
```

```php
$this->db()->fetch('SELECT * FROM products WHERE id = ?', [$id]);
$this->db()->insert('messages', [...]);
$this->db()->update('users', ['name' => $name], 'id = ?', [$id]);
$this->db()->delete('messages', 'id = ?', [$id]);
```

Never concatenate user input into SQL.

---

## Authentication and admin

Public pages work without login.

After sign in (`/login`), you land on `/admin`:

| URL | Purpose | Roles |
|---|---|---|
| `/admin` | Dashboard | admin, editor |
| `/admin/pages` | Create / edit / delete content pages | admin, editor |
| `/admin/media` | Upload and manage images | admin, editor |
| `/admin/messages` | Contact form inbox | admin, editor |
| `/admin/products` | Work items CRUD | admin |
| `/admin/users` | Users and roles | admin |
| `/admin/account` | Change password | admin, editor |

Permissions are checked with:

```php
$this->auth()->can('pages.manage');
$this->auth()->requirePermission('products.manage');
```

Roles: `admin`, `editor`. Controllers live under `app/Controllers/Admin/`. File-based admin work uses `app/Services/` (`ContentPageService`, `MediaService`).

See [Admin](admin.md) and [Architecture](architecture.md) for how the admin layer fits the rest of the system.

---

## Uploads

```php
$filename = $this->app->upload()->store(
    $this->request()->files['photo'] ?? [],
    $this->app->basePath('public/uploads'),
    ['jpg', 'jpeg', 'png', 'webp'],
    2_000_000,
);
```

Public uploads: `public/uploads/` (PHP execution disabled).  
Private files: `storage/uploads/`.

---

## Config

| Key | Purpose |
|---|---|
| `APP_NAME` | Site name in layouts |
| `APP_URL` | Public base URL (subdirectory installs too) |
| `APP_DEBUG` | Show detailed errors locally |
| `APP_ENV` | `local` or `production` |
| `DB_*` | Database |

---

## Cache and tests

Development (`APP_DEBUG=true`) discovers pages as needed.

Production:

```text
php scripts/cache-pages.php
```

Tests:

```text
php tests/run.php
```

---

## Deploy

1. Document root = `public/`
2. `.env` with `APP_DEBUG=false`, real `APP_URL`, real database
3. `php scripts/migrate.php`
4. `php scripts/cache-pages.php`
5. Writable `storage/` and `public/uploads/`

See [security.md](security.md).

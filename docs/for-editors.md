# For editors

This guide is for people who change website text, titles, and simple pages. You do not need to be a programmer.

**Do not edit** the `core` folder. That is the engine. A mistake there can break the whole site.

---

## Change the site name

1. Open the file named `.env` in the project root.
2. Find `APP_NAME=TNC-CMS`.
3. Change `TNC-CMS` to your brand name.
4. Save and refresh the browser.

---

## Add a new page

Every normal page is a folder under `content/pages`.

The folder name becomes the web address.

| Folder | Address |
|---|---|
| `content/pages/about/index.php` | `/about` |
| `content/pages/our-team/index.php` | `/our-team` |
| `content/pages/index.php` | `/` (home) |

### Steps

1. Copy an existing page folder, for example `content/pages/about`.
2. Rename the copy to something like `our-team` (lowercase, hyphens only).
3. Open `index.php` and change the visible sentences.
4. Save.
5. Visit `http://127.0.0.1:8080/our-team`.

You do **not** register the page anywhere else. TNC-CMS finds it automatically.

---

## Set the page title

### Option A — `page.json` (easiest)

Next to the page, create or edit `page.json`:

```json
{
  "title": "Our team"
}
```

### Option B — inside a PHP page

At the top of `index.php`:

```php
$page = [
    'title' => 'Our team',
];
```

If you set neither, the title is taken from the folder name.

---

## HTML pages (safest to edit)

If the file is named `index.html`, TNC-CMS shows the file as written. PHP inside it is **not** run.

Example: `content/pages/services/index.html`

Prefer this when you only need text and headings.

---

## Change the menu, header, or footer

| What | File |
|---|---|
| Top bar / logo area | `app/Views/partials/header.php` |
| Menu links | `app/Views/partials/navigation.php` |
| Footer | `app/Views/partials/footer.php` |

When you add a new page, also add a menu line in `navigation.php`, or the page exists but will not appear in the menu.

Example menu entry:

```php
'/our-team' => 'Our team',
```

---

## Change colours and style

Edit `public/assets/css/app.css`.

Put images in `public/assets/images/`.

---

## Section with its own layout

If a folder contains `layout.php`, pages in that folder (and folders inside it) use that layout.

Example: `content/pages/services/layout.php` applies to `/services` and `/services/web-development`.

Do not delete `<?= $content ?>` from a layout. That is where the page body is inserted.

---

## What not to touch

| Folder / file | Why |
|---|---|
| `core/` | Engine |
| `bootstrap/` | Startup |
| `storage/` | Logs and sessions, not website content |
| Database passwords in `.env` | Ask a developer |

---

## If a new page does not appear

1. Is the file named exactly `index.php` or `index.html`?
2. Is it inside `content/pages`?
3. Does the URL match the folder name?
4. On a live production site, ask a developer to run `php scripts/cache-pages.php`.

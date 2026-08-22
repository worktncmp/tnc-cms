# For editors

This guide is for people who change website text, titles, and pages. You do not need to be a programmer for most day-to-day work.

**Do not edit** the `core` folder. That is the engine. A mistake there can break the whole site.

---

## Use the admin area (recommended)

The easiest way to manage pages and images is through the admin area after signing in.

1. Go to `/login` (sample admin: `editor@example.com` / `TNC-demo-1`).
2. Open **Pages** to create, edit, or delete HTML pages.
3. Open **Media** to upload images, then insert them from the page editor toolbar.

Full details: [Admin](admin.md).

### What you can do in admin

| Task | Where |
|---|---|
| Create or edit page content | **Pages** → New page / Edit |
| Upload images | **Media** |
| Read contact form messages | **Messages** |
| Change your password | **Account** |

New pages from admin are saved as `index.html` + `page.json` under `content/pages`. The URL path you enter (e.g. `our-team`) becomes `/our-team`.

---

## Change the site name

1. Open the file named `.env` in the project root.
2. Find `APP_NAME=TNC-CMS`.
3. Change `TNC-CMS` to your brand name.
4. Save and refresh the browser.

(An admin settings screen for this may be added later; for now `.env` is the source.)

---

## Add a page by hand (alternative)

Developers sometimes add pages as folders on disk. You can too, if you are comfortable editing files.

Every normal page is a folder under `content/pages`. The folder name becomes the web address.

| Folder | Address |
|---|---|
| `content/pages/about/index.php` | `/about` |
| `content/pages/our-team/index.html` | `/our-team` |
| `content/pages/index.php` | `/` (home) |

### Steps

1. Copy an existing page folder, for example `content/pages/about`.
2. Rename the copy to something like `our-team` (lowercase, hyphens only).
3. Open `index.php` or `index.html` and change the visible content.
4. Save.
5. Visit `http://127.0.0.1:8080/our-team`.

You do **not** register the page anywhere else. TNC-CMS finds it automatically.

Prefer **admin** for HTML pages so content is sanitized and you get the visual editor.

---

## Set the page title

### In admin

Enter the title on the create/edit page form. It is stored in `page.json`.

### On disk — `page.json` (easiest)

Next to the page, create or edit `page.json`:

```json
{
  "title": "Our team"
}
```

### On disk — inside a PHP page

At the top of `index.php`:

```php
$page = [
    'title' => 'Our team',
];
```

If you set neither, the title is taken from the folder name.

---

## HTML vs PHP pages

| File | Editable in admin? | Notes |
|---|---|---|
| `index.html` | Yes | Safest for editor content. PHP inside is not run. |
| `index.php` | Title only | Code template. An admin can **Convert to HTML** to edit the body in admin. |
| `index.html` | Yes | An admin can **Convert to PHP** to add code logic (loses WYSIWYG body editing). |

Example HTML page: `content/pages/services/index.html`

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

Put static images in `public/assets/images/`. User-uploaded images from admin go to `public/uploads/`.

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
3. Does the URL match the folder name (lowercase, hyphens)?
4. On a live production site, ask a developer to run `php scripts/cache-pages.php` if the page was added on disk outside admin.

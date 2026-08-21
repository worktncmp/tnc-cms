# Folder guide

| Path | What it is | Who edits it |
|---|---|---|
| `public/` | Web root. Point the server here only. | Assets and uploads |
| `public/index.php` | Front controller | Rarely |
| `public/assets/` | CSS, JS, images, fonts | Editors and developers |
| `public/uploads/` | Public uploaded files | The app |
| `content/pages/` | Normal pages. Folder name = URL. | Editors and developers |
| `app/routes.php` | Extra URLs that need PHP logic | Developers |
| `app/Controllers/` | Form posts, database pages, admin | Developers |
| `app/Controllers/Admin/` | Protected admin screens | Developers |
| `app/Views/admin/` | Admin page templates | Developers |
| `app/Models/` | Table queries | Developers |
| `app/Views/layouts/` | Page frames (`default.php`, `blank.php`) | Developers |
| `app/Views/partials/` | Header, footer, menu | Editors (carefully) / developers |
| `app/Views/components/` | Reusable snippets | Developers |
| `app/Views/errors/` | 404, 403, 405, 500 | Developers |
| `config/` | PHP config loaded from `.env` | Developers |
| `.env` | Secrets and environment (not committed) | Developers |
| `core/` | TNC-CMS engine | Do not edit for a normal site |
| `bootstrap/` | Starts the app | Do not edit for a normal site |
| `storage/` | Logs, cache, sessions | Not a URL |
| `database/` | SQL schema files | Developers |
| `scripts/` | migrate, cache, create-user | Developers |
| `tests/` | Built-in tests | Developers |
| `docs/` | These guides | Everyone |

## Home page

`content/pages/index.php` → `/`

A folder named `home` would be `/home`, not the home page.

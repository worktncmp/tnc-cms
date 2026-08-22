# Security

TNC-CMS includes sensible defaults. Site-specific code can still introduce holes. Treat this as a checklist.

## Server

- Document root must be `public/`
- Project root has a deny-all `.htaccess` if the root is served by mistake
- `storage/` is not a public URL

## Output

- Print user-provided text with `e()`
- Prefer `index.html` for editor-only pages (PHP is not executed)

## Input and SQL

- Use bound parameters (`?`)
- Never concatenate request data into SQL

## Sessions and CSRF

- Every POST / PUT / PATCH / DELETE form needs `<?= csrf_field() ?>`
- Cookies: HttpOnly, SameSite=Lax, Secure on HTTPS
- Login regenerates the session id

## Passwords

Use `Auth::hash()` / `password_hash`. Never store plain passwords.

## Uploads

- Extension allowlist + MIME check
- Dangerous types (PHP, HTML, JS, SVG, …) are refused
- Stored filenames are random
- `public/uploads/` has PHP execution disabled

## Admin

- `/admin` and related URLs require sign-in
- Actions check role permissions (`admin` vs `editor`)
- HTML saved from the page editor is sanitized before write
- Change or remove the sample `admin@example.com` and `editor@example.com` users before go-live

## Errors

Production: `APP_DEBUG=false`. Visitors see generic error pages. Details go to `storage/logs/`.

## Before go-live

1. `APP_DEBUG=false`
2. Strong database password, not in git
3. HTTPS
4. `php scripts/cache-pages.php` after page changes (admin saves clear cache automatically; run manually after editing files on disk)
5. Change or remove the sample demo users (`admin@example.com`, `editor@example.com`)
6. `storage/` writable, not listable

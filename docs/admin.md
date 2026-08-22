# Admin

The admin area is for signed-in users. Public pages still work without login.

## Open it

1. Start the site (see [getting-started.md](getting-started.md)).
2. Go to `/login`.
3. Sample admin: `editor@example.com` / `TNC-demo-1`
4. You land on `/admin` (or the page you tried to open before signing in).

## Dashboard

`/admin` shows counts and shortcuts for the sections you are allowed to use.

## Pages

| Action | Where |
|---|---|
| List pages | `/admin/pages` |
| Create a page | `/admin/pages/create` |
| Edit a page | Edit link in the list |
| Preview | Preview button on the edit screen |
| Delete a page | Delete link (not for home) |

New pages are saved as `index.html` + `page.json` under `content/pages`.

### Editor help

- HTML pages use a **visual editor** (TinyMCE): bold, lists, links, tables, images, and a Code view for raw HTML.
- It is **not** the WordPress block editor. No Gutenberg blocks, themes, or plugins.
- Needs an internet connection the first time (editor loads from a CDN). If the CDN is blocked, simple HTML tools are used instead.
- For images: open **Media**, upload, copy the URL, then use the image button in the editor.
- Existing `index.php` “code” pages: change the title anytime. An **admin** can click **Convert to HTML** to make the body editable in admin (this replaces the PHP file).
- Saved HTML is lightly cleaned (scripts and inline event handlers are stripped).

## Media

`/admin/media`

- Upload jpg / jpeg / png / gif / webp (max 2 MB)
- Copy URL or copy a ready `<img>` tag
- Delete unused files

Files are stored in `public/uploads/`.

## Messages

Read and delete contact form submissions.

## Products

Create, edit, and delete Work items shown on `/products`.  
**Admin role only.**

## Users and roles

| Role | Can do |
|---|---|
| **admin** | Pages, media, messages, products, users, account |
| **editor** | Pages, media, messages, account |

Manage users at `/admin/users` (admin only).

```text
php scripts/create-user.php writer@example.com a-strong-password "Writer" editor
```

## Account

Change your own password at `/admin/account`.

## Login redirect

If you open `/admin/pages` while logged out, you get 403 → Sign in → after login you return to `/admin/pages`.

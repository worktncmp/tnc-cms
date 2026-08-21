# Admin

The admin area is for signed-in users. Public pages still work without login.

## Open it

1. Start the site (see [getting-started.md](getting-started.md)).
2. Go to `/login`.
3. Sample admin: `editor@example.com` / `TNC-demo-1`
4. You land on `/admin`.

## Dashboard

`/admin` shows counts and shortcuts for the sections you are allowed to use.

## Pages (edit while logged in)

| Action | Where |
|---|---|
| List pages | `/admin/pages` |
| Create a page | `/admin/pages/create` |
| Edit a page | Edit link in the list |
| Delete a page | Delete link (not for home) |

New pages are saved as `index.html` + `page.json` under `content/pages`.  
Existing `index.php` pages are “code” pages: you can change the **title**, but the body is edited in the project files by a developer.

## Messages

Read and delete contact form submissions.

## Products

Create, edit, and delete Work items shown on `/products`.  
**Admin role only.**

## Users and roles

| Role | Can do |
|---|---|
| **admin** | Pages, messages, products, users, account |
| **editor** | Pages, messages, account (not products or users) |

Manage users at `/admin/users` (admin only).

Create a user from the terminal:

```text
php scripts/create-user.php writer@example.com a-strong-password "Writer" editor
```

## Account

Change your own password at `/admin/account`.

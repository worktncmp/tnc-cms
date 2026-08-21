# Getting started

This guide gets TNC-CMS running on your computer.

## What you need

- PHP **8.2 or newer**
- A terminal
- A browser

You do **not** need Composer, Node, or MySQL for local learning (SQLite is used by default).

## 1. Open the project

In a terminal, go to the project folder (the folder that contains `public`, `app`, and `core`).

## 2. Check PHP

```text
php -v
```

## 3. Configure the site

If `.env` is missing, copy it:

```text
copy .env.example .env
```

(On Mac/Linux: `cp .env.example .env`)

Open `.env`. The important lines:

```text
APP_NAME=TNC-CMS
APP_URL=http://127.0.0.1:8080
APP_DEBUG=true
DB_DRIVER=sqlite
```

`APP_URL` must match the address you use in the browser.

## 4. Create the database

```text
php scripts/migrate.php
```

This creates tables and a sample login user.

## 5. Start the website

```text
php -S 127.0.0.1:8080 -t public public/router.php
```

Leave that window open. Visit:

**http://127.0.0.1:8080**

## 6. Sign in (optional)

- Email: `editor@example.com`
- Password: `TNC-demo-1`

## What next?

- Change page text → [For editors](for-editors.md)
- Add forms or database pages → [For developers](for-developers.md)
- See every folder explained → [Folder guide](folder-guide.md)

## Production note

On a live server, the web root must be the `public` folder only. See [Security](security.md).

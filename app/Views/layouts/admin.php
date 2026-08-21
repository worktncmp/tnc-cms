<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Admin') ?> — <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="admin-body">
    <header class="admin-top">
        <div class="wrap header-inner">
            <a class="logo" href="<?= url('/admin') ?>"><?= e($appName) ?> Admin</a>
            <nav class="site-nav" aria-label="Admin">
                <div class="nav-links is-open" id="menu" style="display:flex">
                    <a href="<?= url('/admin') ?>"<?= ($currentPath ?? '') === '/admin' ? ' aria-current="page"' : '' ?>>Dashboard</a>
                    <?php if (!empty($canPages)): ?>
                        <a href="<?= url('/admin/pages') ?>"<?= str_starts_with($currentPath ?? '', '/admin/pages') ? ' aria-current="page"' : '' ?>>Pages</a>
                    <?php endif; ?>
                    <?php if (!empty($canMessages)): ?>
                        <a href="<?= url('/admin/messages') ?>"<?= str_starts_with($currentPath ?? '', '/admin/messages') ? ' aria-current="page"' : '' ?>>Messages</a>
                    <?php endif; ?>
                    <?php if (!empty($canProducts)): ?>
                        <a href="<?= url('/admin/products') ?>"<?= str_starts_with($currentPath ?? '', '/admin/products') ? ' aria-current="page"' : '' ?>>Products</a>
                    <?php endif; ?>
                    <?php if (!empty($canUsers)): ?>
                        <a href="<?= url('/admin/users') ?>"<?= str_starts_with($currentPath ?? '', '/admin/users') ? ' aria-current="page"' : '' ?>>Users</a>
                    <?php endif; ?>
                    <a href="<?= url('/admin/account') ?>"<?= ($currentPath ?? '') === '/admin/account' ? ' aria-current="page"' : '' ?>>Account</a>
                    <a href="<?= url('/') ?>">View site</a>
                    <form method="post" action="<?= url('/logout') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="button-quiet">Sign out</button>
                    </form>
                </div>
            </nav>
        </div>
    </header>
    <main class="wrap admin-main">
        <?php if (!empty($flashSuccess)): ?>
            <?= component('alert', ['type' => 'success', 'message' => $flashSuccess]) ?>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <?= component('alert', ['type' => 'error', 'message' => $flashError]) ?>
        <?php endif; ?>
        <?= $content ?>
    </main>
</body>
</html>

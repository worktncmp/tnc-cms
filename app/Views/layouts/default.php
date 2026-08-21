<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $appName) ?> — <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <?= partial('header', ['appName' => $appName, 'currentPath' => $currentPath, 'currentUser' => $currentUser]) ?>
    <main class="wrap">
        <?php if (!empty($flashSuccess)): ?>
            <?= component('alert', ['type' => 'success', 'message' => $flashSuccess]) ?>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <?= component('alert', ['type' => 'error', 'message' => $flashError]) ?>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <?= partial('footer', ['appName' => $appName]) ?>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

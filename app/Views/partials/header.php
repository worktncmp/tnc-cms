<header class="site-header">
    <div class="wrap header-inner">
        <a class="logo" href="<?= url('/') ?>"><?= e($appName) ?></a>
        <?= partial('navigation', ['currentPath' => $currentPath ?? '/', 'currentUser' => $currentUser ?? null]) ?>
    </div>
</header>

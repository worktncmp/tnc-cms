<section class="error-page">
    <p class="eyebrow">403</p>
    <h1>You cannot open this page</h1>
    <p><?= e($message ?? 'This action is not allowed.') ?></p>
    <p>
        <?php if (!empty($showLogin)): ?>
            <a class="button" href="<?= url('/login') ?>">Sign in</a>
        <?php endif; ?>
        <a class="button button-quiet" href="<?= url('/') ?>">Back to home</a>
    </p>
</section>

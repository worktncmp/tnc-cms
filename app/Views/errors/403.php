<section class="error-page">
    <p class="eyebrow">403</p>
    <h1>You cannot open this page</h1>
    <p><?= e($message ?? 'This action is not allowed.') ?></p>
    <p><a class="button" href="<?= url('/') ?>">Back to home</a></p>
</section>

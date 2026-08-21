<section class="error-page">
    <p class="eyebrow">500</p>
    <h1>Something went wrong</h1>
    <p><?= e($message ?? 'Please try again in a moment.') ?></p>
    <?php if (!empty($debug) && !empty($trace)): ?>
        <pre class="debug"><?= e($trace) ?></pre>
    <?php endif; ?>
    <p><a class="button" href="<?= url('/') ?>">Back to home</a></p>
</section>

<article class="card">
    <?php if (!empty($title)): ?>
        <h3><?= e($title) ?></h3>
    <?php endif; ?>
    <?php if (!empty($body)): ?>
        <p><?= e($body) ?></p>
    <?php endif; ?>
    <?php if (!empty($href) && !empty($link)): ?>
        <p><a href="<?= e($href) ?>"><?= e($link) ?></a></p>
    <?php endif; ?>
</article>

<section>
    <p class="eyebrow">Selected work</p>
    <h1>Projects</h1>
    <p>These records come from the database through a controller, a model, and a view — not from a content page.</p>
    <div class="grid">
        <?php foreach ($products as $product): ?>
            <?= component('card', [
                'title' => $product['title'],
                'body' => $product['summary'],
                'href' => route('product.show', ['id' => (string) $product['id']]),
                'link' => 'View project',
            ]) ?>
        <?php endforeach; ?>
    </div>
    <?php if ($products === []): ?>
        <p>No projects yet. Run <code>php scripts/migrate.php</code> to load the sample data.</p>
    <?php endif; ?>
</section>

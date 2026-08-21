<section>
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow">Admin</p>
            <h1>Media</h1>
            <p>Upload images for your pages. Copy a URL, then paste it into an <code>&lt;img&gt;</code> tag in the page editor.</p>
        </div>
    </div>

    <form method="post" action="<?= url('/admin/media') ?>" enctype="multipart/form-data" class="stack narrow" style="margin-bottom: 2rem;">
        <?= csrf_field() ?>
        <label>
            Image file
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" required>
        </label>
        <p class="muted">Allowed: <?= e(implode(', ', $allowed)) ?>. Max size 2&nbsp;MB.</p>
        <button type="submit">Upload</button>
    </form>

    <?php if ($files === []): ?>
        <p>No images yet.</p>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($files as $file): ?>
                <article class="media-card">
                    <img src="<?= e($file['url']) ?>" alt="<?= e($file['name']) ?>" loading="lazy">
                    <p class="media-name"><?= e($file['name']) ?></p>
                    <input class="media-url" type="text" readonly value="<?= e($file['url']) ?>">
                    <div class="table-actions">
                        <button type="button" class="button-quiet js-copy-url" data-url="<?= e($file['url']) ?>">Copy URL</button>
                        <button type="button" class="button-quiet js-insert-img" data-url="<?= e($file['url']) ?>">Img tag</button>
                        <form method="post" action="<?= url('/admin/media/delete') ?>" class="inline-form" onsubmit="return confirm('Delete this image?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="name" value="<?= e($file['name']) ?>">
                            <button type="submit" class="linkish">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

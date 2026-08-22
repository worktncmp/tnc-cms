<?php
$isEdit = is_array($page ?? null);
$pathValue = old('path', $isEdit ? (string) $page['path'] : '');
$titleValue = old('title', $isEdit ? (string) $page['title'] : '');
$bodyValue = old('body', $isEdit ? (string) ($page['body'] ?? '') : '');
$editable = $isEdit ? !empty($page['editable']) : true;
$previewUrl = $isEdit ? url($page['url']) : null;
$mediaUrl = url('/admin/media');
?>
<section class="editor-wide">
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow"><a href="<?= url('/admin/pages') ?>">Pages</a></p>
            <h1><?= $isEdit ? 'Edit page' : 'New page' ?></h1>
        </div>
        <div class="table-actions">
            <?php if ($previewUrl): ?>
                <a class="button button-quiet" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener">Preview</a>
            <?php endif; ?>
            <?php if (!empty($canMedia)): ?>
                <a class="button button-quiet" href="<?= e($mediaUrl) ?>" target="_blank" rel="noopener">Media library</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isEdit && !$editable): ?>
        <div class="card" style="margin-bottom: 1rem;">
            <p>This page is a PHP template (<code>index.php</code>). You can change the title here.</p>
            <p class="muted">To edit the body in admin, an admin can convert it to HTML. That replaces the PHP file with <code>index.html</code>.</p>
            <?php if (!empty($isAdmin)): ?>
                <form method="post" action="<?= url('/admin/pages/convert') ?>" onsubmit="return confirm('Convert this PHP page to editable HTML? The PHP file will be replaced.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="path" value="<?= e((string) $page['path']) ?>">
                    <button type="submit">Convert to HTML</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>" class="stack" id="page-editor-form" novalidate>
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="path" value="<?= e((string) $page['path']) ?>">
            <p>URL: <strong><?= e($page['url']) ?></strong></p>
        <?php else: ?>
            <label>
                Path (becomes the URL)
                <input type="text" name="path" id="page-path" value="<?= e($pathValue) ?>" placeholder="our-team" pattern="[a-z0-9]+(-[a-z0-9]+)*(/[a-z0-9]+(-[a-z0-9]+)*)*" required>
            </label>
            <p class="muted">Use lowercase only, hyphens instead of spaces. <code>our-team</code> → <code>/our-team</code>. Not <code>Our Team</code>.</p>
        <?php endif; ?>

        <label>
            Title
            <input type="text" name="title" value="<?= e($titleValue) ?>" required>
        </label>

        <?php if ($editable): ?>
            <div
                class="wysiwyg-wrap"
                data-media-url="<?= e($mediaUrl) ?>"
                data-uploads-base="<?= e(url('uploads/')) ?>"
            >
                <p class="muted" style="margin-bottom: 0.4rem;">
                    Visual editor (TinyMCE). Upload images in <a href="<?= e($mediaUrl) ?>" target="_blank" rel="noopener">Media</a>,
                    then use the image button and paste the file URL. Switch to <strong>Code</strong> in the editor for raw HTML.
                </p>
                <label class="sr-only" for="page-body">Content</label>
                <textarea id="page-body" name="body" rows="16"><?= e($bodyValue) ?></textarea>
                <p id="page-body-error" class="alert alert-error" hidden>Please add some page content before saving.</p>
                <noscript>
                    <p class="muted">JavaScript is off, so the visual editor is unavailable. Edit HTML in the box above.</p>
                </noscript>
            </div>
        <?php endif; ?>

        <button type="submit"><?= $isEdit ? 'Save page' : 'Create page' ?></button>
    </form>
</section>

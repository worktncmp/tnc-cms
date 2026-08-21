<?php
$isEdit = is_array($page ?? null);
$pathValue = old('path', $isEdit ? (string) $page['path'] : '');
$titleValue = old('title', $isEdit ? (string) $page['title'] : '');
$bodyValue = old('body', $isEdit ? (string) ($page['body'] ?? '') : '');
$editable = $isEdit ? !empty($page['editable']) : true;
$previewUrl = $isEdit ? url($page['url']) : null;
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
                <a class="button button-quiet" href="<?= url('/admin/media') ?>" target="_blank" rel="noopener">Media library</a>
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

    <form method="post" action="<?= e($action) ?>" class="stack" id="page-editor-form">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="path" value="<?= e((string) $page['path']) ?>">
            <p>URL: <strong><?= e($page['url']) ?></strong></p>
        <?php else: ?>
            <label>
                Path (becomes the URL)
                <input type="text" name="path" value="<?= e($pathValue) ?>" placeholder="our-team or services/pricing" required>
            </label>
            <p class="muted">Lowercase letters, numbers, hyphens, and optional slashes. Example: <code>our-team</code> → <code>/our-team</code></p>
        <?php endif; ?>

        <label>
            Title
            <input type="text" name="title" value="<?= e($titleValue) ?>" required>
        </label>

        <?php if ($editable): ?>
            <div>
                <p class="muted" style="margin-bottom: 0.4rem;">Content tips: use simple HTML. Upload images in Media, copy the URL, then use <strong>Image</strong> below.</p>
                <div class="editor-toolbar" role="toolbar" aria-label="Formatting">
                    <button type="button" class="button-quiet" data-wrap="<strong>" data-wrap-end="</strong>">Bold</button>
                    <button type="button" class="button-quiet" data-wrap="<em>" data-wrap-end="</em>">Italic</button>
                    <button type="button" class="button-quiet" data-wrap="<h2>" data-wrap-end="</h2>">Heading</button>
                    <button type="button" class="button-quiet" data-wrap="<p>" data-wrap-end="</p>">Paragraph</button>
                    <button type="button" class="button-quiet" data-wrap='<a href="https://">' data-wrap-end="</a>">Link</button>
                    <button type="button" class="button-quiet" data-insert='<img src="" alt="">'>Image</button>
                    <button type="button" class="button-quiet" data-wrap="<ul>\n  <li>" data-wrap-end="</li>\n</ul>">List</button>
                </div>
                <label class="sr-only" for="page-body">Content</label>
                <textarea id="page-body" name="body" rows="16" required><?= e($bodyValue) ?></textarea>
            </div>
        <?php endif; ?>

        <button type="submit"><?= $isEdit ? 'Save page' : 'Create page' ?></button>
    </form>
</section>

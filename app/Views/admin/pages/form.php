<?php
$isEdit = is_array($page ?? null);
$pathValue = old('path', $isEdit ? (string) $page['path'] : '');
$titleValue = old('title', $isEdit ? (string) $page['title'] : '');
$bodyValue = old('body', $isEdit ? (string) ($page['body'] ?? '') : '');
$editable = $isEdit ? !empty($page['editable']) : true;
$previewUrl = $isEdit ? url($page['url']) : null;
$mediaUrl = url('/admin/media');
$mediaListUrl = url('/admin/media/list.json');
$existingPaths = $existingPaths ?? [];
$siteBase = rtrim((string) (getenv('APP_URL') ?: ''), '/');
if ($siteBase === '') {
    $siteBase = '';
}
?>
<section class="editor-wide">
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow"><a href="<?= url('/admin/pages') ?>">Pages</a></p>
            <h1><?= $isEdit ? 'Edit page' : 'New page' ?></h1>
        </div>
        <div class="table-actions">
            <?php if ($previewUrl): ?>
                <a class="button button-quiet" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener">View live</a>
            <?php endif; ?>
            <?php if (!empty($canMedia)): ?>
                <a class="button button-quiet" href="<?= e($mediaUrl) ?>" target="_blank" rel="noopener">Media library</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isEdit && !$editable): ?>
        <div class="card editor-notice">
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

    <form
        method="post"
        action="<?= e($action) ?>"
        class="page-editor"
        id="page-editor-form"
        novalidate
        data-existing-paths="<?= e(json_encode(array_values($existingPaths), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
        data-media-list-url="<?= e($mediaListUrl) ?>"
        data-site-base="<?= e($siteBase) ?>"
    >
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="path" value="<?= e((string) $page['path']) ?>">
        <?php endif; ?>

        <div class="editor-layout">
            <aside class="editor-sidebar card">
                <div class="stack">
                    <label>
                        Title
                        <input type="text" name="title" id="page-title" value="<?= e($titleValue) ?>" required autocomplete="off">
                    </label>

                    <?php if (!$isEdit): ?>
                        <label>
                            URL path
                            <input
                                type="text"
                                name="path"
                                id="page-path"
                                value="<?= e($pathValue) ?>"
                                placeholder="our-team"
                                pattern="[a-z0-9]+(-[a-z0-9]+)*(/[a-z0-9]+(-[a-z0-9]+)*)*"
                                required
                                autocomplete="off"
                            >
                        </label>
                        <p id="page-path-preview" class="url-preview muted">
                            <?php if ($pathValue !== ''): ?>
                                Will be published at <strong><?= e('/' . ltrim($pathValue, '/')) ?></strong>
                            <?php else: ?>
                                Path updates as you type the title
                            <?php endif; ?>
                        </p>
                        <p id="page-path-error" class="field-error" hidden></p>
                    <?php else: ?>
                        <div class="url-preview">
                            <span class="muted">Public URL</span>
                            <p><a href="<?= e($previewUrl) ?>" target="_blank" rel="noopener"><strong><?= e($page['url']) ?></strong></a></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($editable): ?>
                        <p class="muted editor-tip">
                            Use the visual editor for content. Images come from the Media library — click the image button in the toolbar.
                        </p>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="editor-main">
                <?php if ($editable): ?>
                    <div
                        class="wysiwyg-wrap"
                        data-media-url="<?= e($mediaUrl) ?>"
                        data-media-list-url="<?= e($mediaListUrl) ?>"
                        data-uploads-base="<?= e(url('uploads/')) ?>"
                    >
                        <label class="sr-only" for="page-body">Content</label>
                        <textarea id="page-body" name="body" rows="16"><?= e($bodyValue) ?></textarea>
                        <p id="page-body-error" class="field-error" hidden>Please add some page content before saving.</p>
                        <noscript>
                            <p class="muted">JavaScript is off, so the visual editor is unavailable. Edit HTML in the box above.</p>
                        </noscript>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="editor-actions">
            <div class="table-actions">
                <button type="submit" name="stay" value="1"><?= $isEdit ? 'Save' : 'Create page' ?></button>
                <button type="submit" class="button-quiet"><?= $isEdit ? 'Save and close' : 'Create and close' ?></button>
                <a class="button button-quiet" href="<?= url('/admin/pages') ?>">Cancel</a>
            </div>
        </div>
    </form>
</section>

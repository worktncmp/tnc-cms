<?php
$isEdit = is_array($page ?? null);
$pathValue = old('path', $isEdit ? (string) $page['path'] : '');
$titleValue = old('title', $isEdit ? (string) $page['title'] : '');
$bodyValue = old('body', $isEdit ? (string) ($page['body'] ?? '') : '');
$editable = $isEdit ? !empty($page['editable']) : true;
?>
<section class="narrow">
    <p class="eyebrow"><a href="<?= url('/admin/pages') ?>">Pages</a></p>
    <h1><?= $isEdit ? 'Edit page' : 'New page' ?></h1>

    <?php if ($isEdit && !$editable): ?>
        <p class="muted">This page is a PHP template (<code>index.php</code>). You can change the title here. Body content is edited by a developer in the project files.</p>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>" class="stack">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="path" value="<?= e((string) $page['path']) ?>">
            <p>URL: <strong><?= e($page['url']) ?></strong></p>
        <?php else: ?>
            <label>
                Path (becomes the URL)
                <input type="text" name="path" value="<?= e($pathValue) ?>" placeholder="our-team or services/pricing" required>
            </label>
            <p class="muted">Use lowercase letters, numbers, hyphens, and optional slashes. Example: <code>our-team</code> → <code>/our-team</code></p>
        <?php endif; ?>

        <label>
            Title
            <input type="text" name="title" value="<?= e($titleValue) ?>" required>
        </label>

        <?php if ($editable): ?>
            <label>
                Content (HTML allowed)
                <textarea name="body" rows="14" required><?= e($bodyValue) ?></textarea>
            </label>
        <?php endif; ?>

        <button type="submit"><?= $isEdit ? 'Save page' : 'Create page' ?></button>
    </form>
</section>

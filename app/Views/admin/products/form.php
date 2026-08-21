<?php
$isEdit = is_array($product ?? null);
$titleValue = old('title', $isEdit ? (string) $product['title'] : '');
$summaryValue = old('summary', $isEdit ? (string) $product['summary'] : '');
$bodyValue = old('body', $isEdit ? (string) $product['body'] : '');
?>
<section class="narrow">
    <p class="eyebrow"><a href="<?= url('/admin/products') ?>">Products</a></p>
    <h1><?= $isEdit ? 'Edit product' : 'New product' ?></h1>
    <form method="post" action="<?= e($action) ?>" class="stack">
        <?= csrf_field() ?>
        <label>
            Title
            <input type="text" name="title" value="<?= e($titleValue) ?>" required>
        </label>
        <label>
            Summary
            <input type="text" name="summary" value="<?= e($summaryValue) ?>" required>
        </label>
        <label>
            Body
            <textarea name="body" rows="8" required><?= e($bodyValue) ?></textarea>
        </label>
        <button type="submit"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
    </form>
</section>

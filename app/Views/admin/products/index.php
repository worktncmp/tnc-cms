<section>
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow">Admin</p>
            <h1>Products</h1>
            <p>These appear on the public Work pages.</p>
        </div>
        <p><a class="button" href="<?= url('/admin/products/create') ?>">New product</a></p>
    </div>

    <?php if ($products === []): ?>
        <p>No products yet. <a href="<?= url('/admin/products/create') ?>">Create one</a>.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Summary</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= e($product['title']) ?></td>
                        <td><?= e($product['summary']) ?></td>
                        <td class="table-actions">
                            <a href="<?= url('/products/' . $product['id']) ?>">View</a>
                            <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>">Edit</a>
                            <form method="post" action="<?= url('/admin/products/' . $product['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Delete this product?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="linkish">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

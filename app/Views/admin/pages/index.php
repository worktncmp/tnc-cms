<section>
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow">Admin</p>
            <h1>Pages</h1>
            <p>Edit public content under <code>content/pages</code>. New pages are saved as HTML so they stay safe to edit here.</p>
        </div>
        <p><a class="button" href="<?= url('/admin/pages/create') ?>">New page</a></p>
    </div>

    <?php if ($pages === []): ?>
        <p>No pages found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?= e($page['title']) ?></td>
                        <td><a href="<?= url($page['url']) ?>"><?= e($page['url']) ?></a></td>
                        <td><?= e($page['type']) ?><?= $page['editable'] ? '' : ' (code)' ?></td>
                        <td class="table-actions">
                            <a href="<?= url('/admin/pages/edit') . ($page['path'] === '' ? '' : ('?path=' . rawurlencode($page['path']))) ?>">Edit</a>
                            <?php if ($page['path'] !== ''): ?>
                                <form method="post" action="<?= url('/admin/pages/delete') ?>" class="inline-form" onsubmit="return confirm('Delete this page?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="path" value="<?= e($page['path']) ?>">
                                    <button type="submit" class="linkish">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

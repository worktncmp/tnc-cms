<section>
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow">Admin</p>
            <h1>Pages</h1>
            <p>Manage public content under <code>content/pages</code>. HTML pages are editable here; PHP templates need conversion first.</p>
        </div>
        <p><a class="button" href="<?= url('/admin/pages/create') ?>">New page</a></p>
    </div>

    <?php if ($pages === []): ?>
        <div class="card empty-state">
            <h2>No pages yet</h2>
            <p class="muted">Create your first page to add content to the site. Each page gets its own URL, like <code>/about</code> or <code>/services/pricing</code>.</p>
            <p><a class="button" href="<?= url('/admin/pages/create') ?>">Create your first page</a></p>
        </div>
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
                        <td><a href="<?= url($page['url']) ?>" target="_blank" rel="noopener"><?= e($page['url']) ?></a></td>
                        <td>
                            <?php if ($page['editable']): ?>
                                <span class="badge badge-ok">HTML</span>
                            <?php else: ?>
                                <span class="badge badge-muted">PHP</span>
                            <?php endif; ?>
                        </td>
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

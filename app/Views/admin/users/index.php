<section>
    <div class="admin-toolbar">
        <div>
            <p class="eyebrow">Admin</p>
            <h1>Users</h1>
            <p><strong>admin</strong> can manage everything. <strong>editor</strong> can manage pages and messages only.</p>
        </div>
        <p><a class="button" href="<?= url('/admin/users/create') ?>">New user</a></p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['role'] ?? 'editor') ?></td>
                    <td>
                        <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/role') ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <select name="role">
                                <option value="admin"<?= ($user['role'] ?? '') === 'admin' ? ' selected' : '' ?>>admin</option>
                                <option value="editor"<?= ($user['role'] ?? '') === 'editor' ? ' selected' : '' ?>>editor</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

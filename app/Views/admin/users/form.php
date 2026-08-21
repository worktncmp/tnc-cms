<section class="narrow">
    <p class="eyebrow"><a href="<?= url('/admin/users') ?>">Users</a></p>
    <h1>New user</h1>
    <form method="post" action="<?= url('/admin/users') ?>" class="stack">
        <?= csrf_field() ?>
        <label>
            Name
            <input type="text" name="name" value="<?= e(old('name')) ?>" required>
        </label>
        <label>
            Email
            <input type="email" name="email" value="<?= e(old('email')) ?>" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required minlength="8">
        </label>
        <label>
            Role
            <select name="role">
                <option value="editor"<?= old('role', 'editor') === 'editor' ? ' selected' : '' ?>>editor</option>
                <option value="admin"<?= old('role') === 'admin' ? ' selected' : '' ?>>admin</option>
            </select>
        </label>
        <button type="submit">Create user</button>
    </form>
</section>

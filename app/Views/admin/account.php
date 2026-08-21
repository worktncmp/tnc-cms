<section class="narrow">
    <p class="eyebrow">Admin</p>
    <h1>Your account</h1>
    <p>Signed in as <strong><?= e($user['name'] ?? '') ?></strong> (<?= e($user['email'] ?? '') ?>).</p>

    <h2>Change password</h2>
    <form method="post" action="<?= url('/admin/account/password') ?>" class="stack">
        <?= csrf_field() ?>
        <label>
            Current password
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>
        <label>
            New password
            <input type="password" name="password" required minlength="8" autocomplete="new-password">
        </label>
        <label>
            Confirm new password
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </label>
        <button type="submit">Update password</button>
    </form>
</section>

<section class="narrow">
    <p class="eyebrow">Admin</p>
    <h1>Sign in</h1>
    <p>Sign in to open the admin area: messages, products, and your account.</p>
    <form method="post" action="<?= url('/login') ?>" class="stack">
        <?= csrf_field() ?>
        <label>
            Email
            <input type="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="username">
        </label>
        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit">Sign in</button>
    </form>
</section>

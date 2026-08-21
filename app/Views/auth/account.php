<section class="narrow">
    <p class="eyebrow">Account</p>
    <h1>Hello, <?= e($user['name'] ?? 'there') ?></h1>
    <p>You are signed in as <?= e($user['email'] ?? '') ?>.</p>
    <form method="post" action="<?= url('/logout') ?>">
        <?= csrf_field() ?>
        <button type="submit">Sign out</button>
    </form>
</section>

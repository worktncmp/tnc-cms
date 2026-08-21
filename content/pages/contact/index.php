<?php
$page = [
    'title' => 'Contact',
];
?>
<section class="narrow">
    <p class="eyebrow">Contact</p>
    <h1>Tell us about the work.</h1>
    <p>This page is a content file. Sending the form is handled by an explicit POST route and a controller.</p>
    <form method="post" action="<?= url('/contact') ?>" class="stack">
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
            Message
            <textarea name="message" rows="6" required><?= e(old('message')) ?></textarea>
        </label>
        <button type="submit">Send</button>
    </form>
</section>

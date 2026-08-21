<?php
$page = [
    'title' => 'Home',
];
?>
<section class="hero">
    <p class="eyebrow">TNC-CMS</p>
    <h1>A small PHP foundation for real websites.</h1>
    <p>Add a folder under <code>content/pages</code> and the page appears. Use controllers only when you need forms or database logic.</p>
    <p>
        <a class="button" href="<?= url('/about') ?>">How it works</a>
        <a class="button button-quiet" href="<?= url('/contact') ?>">Contact</a>
    </p>
</section>
<section class="grid">
    <?= component('card', [
        'title' => 'Pages from folders',
        'body' => 'Create content/pages/about/index.php and visit /about. No route file needed.',
        'href' => url('/about'),
        'link' => 'About',
    ]) ?>
    <?= component('card', [
        'title' => 'Section layouts',
        'body' => 'Services uses a local layout. Child pages inherit it automatically.',
        'href' => url('/services'),
        'link' => 'Services',
    ]) ?>
    <?= component('card', [
        'title' => 'Controllers when needed',
        'body' => 'Work pages load from the database through a controller and a route.',
        'href' => url('/products'),
        'link' => 'Work',
    ]) ?>
</section>

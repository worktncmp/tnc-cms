<?php
$links = [
    '/' => 'Home',
    '/about' => 'About',
    '/services' => 'Services',
    '/products' => 'Work',
    '/contact' => 'Contact',
];
$current = $currentPath ?? '/';
?>
<nav class="site-nav" aria-label="Main">
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu">Menu</button>
    <div class="nav-links" id="menu">
        <?php foreach ($links as $href => $label): ?>
            <a href="<?= url($href) ?>"<?= $current === $href || ($href !== '/' && str_starts_with($current, $href)) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
        <?php if (!empty($currentUser)): ?>
            <a href="<?= url('/account') ?>"<?= $current === '/account' ? ' aria-current="page"' : '' ?>>Account</a>
        <?php else: ?>
            <a href="<?= url('/login') ?>">Sign in</a>
        <?php endif; ?>
    </div>
</nav>

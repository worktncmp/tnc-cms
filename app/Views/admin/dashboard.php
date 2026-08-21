<section>
    <p class="eyebrow">Admin · <?= e(ucfirst((string) $role)) ?></p>
    <h1>Dashboard</h1>
    <p>Signed in as <?= e($currentUser['email'] ?? '') ?>. Use the sections below for day-to-day site work.</p>

    <div class="grid" style="margin: 1.5rem 0;">
        <?php if (!empty($canPages)): ?>
            <article class="card">
                <h3><?= (int) $pageCount ?></h3>
                <p>Content pages</p>
                <p><a href="<?= url('/admin/pages') ?>">Edit pages</a></p>
            </article>
        <?php endif; ?>
        <?php if (!empty($canMessages)): ?>
            <article class="card">
                <h3><?= (int) $messageCount ?></h3>
                <p>Contact messages</p>
                <p><a href="<?= url('/admin/messages') ?>">View messages</a></p>
            </article>
        <?php endif; ?>
        <?php if (!empty($canProducts)): ?>
            <article class="card">
                <h3><?= (int) $productCount ?></h3>
                <p>Products / work items</p>
                <p><a href="<?= url('/admin/products') ?>">Manage products</a></p>
            </article>
        <?php endif; ?>
        <?php if (!empty($canUsers)): ?>
            <article class="card">
                <h3><?= (int) $userCount ?></h3>
                <p>Users</p>
                <p><a href="<?= url('/admin/users') ?>">Manage users</a></p>
            </article>
        <?php endif; ?>
    </div>

    <?php if (!empty($canMessages)): ?>
        <h2>Recent messages</h2>
        <?php if ($recentMessages === []): ?>
            <p>No messages yet. Submissions from the contact form will appear here.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMessages as $message): ?>
                        <tr>
                            <td><?= e($message['name']) ?></td>
                            <td><?= e($message['email']) ?></td>
                            <td><?= e($message['created_at']) ?></td>
                            <td><a href="<?= url('/admin/messages/' . $message['id']) ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</section>

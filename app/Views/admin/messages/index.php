<section>
    <p class="eyebrow">Admin</p>
    <h1>Messages</h1>
    <p>Contact form submissions from the public site.</p>

    <?php if ($messages === []): ?>
        <p>No messages yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Preview</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?= e($message['name']) ?></td>
                        <td><?= e($message['email']) ?></td>
                        <td><?= e(strlen((string) $message['body']) > 60 ? substr((string) $message['body'], 0, 57) . '...' : (string) $message['body']) ?></td>
                        <td><?= e($message['created_at']) ?></td>
                        <td><a href="<?= url('/admin/messages/' . $message['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

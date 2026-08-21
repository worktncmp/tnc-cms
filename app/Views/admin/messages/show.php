<article class="narrow">
    <p class="eyebrow"><a href="<?= url('/admin/messages') ?>">Messages</a></p>
    <h1><?= e($message['name']) ?></h1>
    <p><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></p>
    <p class="muted">Received <?= e($message['created_at']) ?></p>
    <div class="card" style="margin: 1.2rem 0;">
        <p style="margin:0; white-space: pre-wrap;"><?= e($message['body']) ?></p>
    </div>
    <form method="post" action="<?= url('/admin/messages/' . $message['id'] . '/delete') ?>" onsubmit="return confirm('Delete this message?');">
        <?= csrf_field() ?>
        <button type="submit">Delete message</button>
    </form>
</article>

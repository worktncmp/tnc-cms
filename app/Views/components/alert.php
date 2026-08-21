<?php
$type = $type ?? 'info';
$message = $message ?? '';
?>
<div class="alert alert-<?= e($type) ?>" role="status"><?= e($message) ?></div>

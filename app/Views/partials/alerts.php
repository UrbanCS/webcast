<?php
defined('LSB_APP') or exit;
?>
<?php if (!empty($messages)): ?>
    <div class="alerts-stack" role="status" aria-live="polite">
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?= e((string) $message['type']); ?>">
                <?= e((string) $message['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

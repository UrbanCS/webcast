<?php
defined('LSB_APP') or exit;
?>
<link rel="stylesheet" href="<?= e(asset_url('css/style.css')); ?>">
<div class="lsb-embed-shell">
    <?= $content; ?>
</div>
<script src="<?= e(asset_url('js/app.js')); ?>" defer></script>

<?php
defined('LSB_APP') or exit;
$flashMessages = flash_messages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? lang('login')); ?> · <?= e(lang('app_name')); ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css')); ?>">
</head>
<body class="lsb-auth-body">
    <main class="auth-shell">
        <?= render_partial('partials/alerts', ['messages' => $flashMessages]); ?>
        <?= $content; ?>
    </main>
</body>
</html>

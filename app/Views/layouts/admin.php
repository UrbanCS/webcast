<?php
defined('LSB_APP') or exit;
$flashMessages = flash_messages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? lang('dashboard')); ?> · <?= e(lang('app_name')); ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css')); ?>">
</head>
<body class="lsb-admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">LS</span>
                <div>
                    <strong><?= e(lang('app_name')); ?></strong>
                    <span><?= e(lang('dashboard')); ?></span>
                </div>
            </div>
            <nav class="admin-sidebar__nav" aria-label="Navigation administrateur">
                <a href="<?= e(base_url('admin.php')); ?>"><?= e(lang('dashboard')); ?></a>
                <a href="<?= e(base_url('admin/events/create/')); ?>"><?= e(lang('create_event')); ?></a>
                <a href="<?= e(base_url()); ?>" target="_blank" rel="noopener"><?= e(lang('public_site')); ?></a>
                <a href="<?= e(base_url('logout.php')); ?>"><?= e(lang('logout')); ?></a>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="admin-topbar__eyebrow"><?= e(lang('app_name')); ?></p>
                    <h1><?= e($pageTitle ?? lang('dashboard')); ?></h1>
                </div>
                <a class="button button-primary" href="<?= e(base_url('admin/events/create/')); ?>"><?= e(lang('new_event')); ?></a>
            </header>

            <?= render_partial('partials/alerts', ['messages' => $flashMessages]); ?>
            <?= $content; ?>
        </div>
    </div>
    <script src="<?= e(asset_url('js/admin.js')); ?>" defer></script>
</body>
</html>

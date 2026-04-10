<?php
defined('LSB_APP') or exit;
$flashMessages = flash_messages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? lang('app_name')); ?></title>
    <meta name="description" content="Module webcast Lifstories pour diffusions YouTube Live, replays et téléchargements.">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')); ?>">
    <style><?= inline_asset_contents('css/style.css'); ?></style>
</head>
<body class="lsb-public-body">
    <div class="site-shell">
        <header class="site-header">
            <div class="site-header__inner">
                <a class="site-brand" href="<?= e(base_url()); ?>">
                    <span class="site-brand__mark">LS</span>
                    <span class="site-brand__text"><?= e(lang('app_name')); ?></span>
                </a>
                <nav class="site-nav" aria-label="Navigation principale">
                    <a href="<?= e(base_url()); ?>"><?= e(lang('events')); ?></a>
                    <a href="<?= e(base_url('admin.php')); ?>"><?= e(lang('dashboard')); ?></a>
                </nav>
            </div>
        </header>

        <main class="site-main">
            <?= render_partial('partials/alerts', ['messages' => $flashMessages]); ?>
            <?= $content; ?>
        </main>

        <footer class="site-footer">
            <div class="site-footer__inner">
                <p><?= e(lang('app_name')); ?> · YouTube Live, rediffusion et téléchargement sur hébergement cPanel.</p>
            </div>
        </footer>
    </div>
    <script src="<?= e(asset_url('js/app.js')); ?>" defer></script>
</body>
</html>

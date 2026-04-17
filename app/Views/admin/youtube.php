<?php
defined('LSB_APP') or exit;
?>
<section class="panel panel-spaced">
    <div>
        <p class="admin-topbar__eyebrow"><?= e(lang('youtube_integration')); ?></p>
        <h2><?= e(lang('youtube_channel_connection')); ?></h2>
        <p class="muted-text"><?= e(lang('youtube_integration_intro')); ?></p>
    </div>

    <?php if (!$configured): ?>
        <div class="alert alert-info">
            <?= e(lang('youtube_config_missing')); ?>
        </div>

        <div class="config-card">
            <strong><?= e(lang('youtube_redirect_uri')); ?></strong>
            <code><?= e($redirectUri); ?></code>
        </div>

        <p class="form-help"><?= e(lang('youtube_config_help')); ?></p>
    <?php else: ?>
        <div class="config-card">
            <strong><?= e(lang('youtube_redirect_uri')); ?></strong>
            <code><?= e($redirectUri); ?></code>
        </div>

        <div class="config-card">
            <strong><?= e(lang('youtube_default_privacy')); ?></strong>
            <span><?= e($privacyStatus); ?></span>
        </div>

        <?php if ($connectionError !== null): ?>
            <div class="alert alert-error"><?= e($connectionError); ?></div>
        <?php endif; ?>

        <?php if ($connected): ?>
            <div class="alert alert-success">
                <?= e(lang('youtube_connected')); ?>
                <?php if (!empty($channel['title'])): ?>
                    <strong><?= e($channel['title']); ?></strong>
                <?php endif; ?>
            </div>

            <form method="post" action="<?= e(base_url('admin/youtube/disconnect.php')); ?>" data-confirm="<?= e(lang('youtube_disconnect_confirm')); ?>">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token('youtube_disconnect')); ?>">
                <button class="button button-danger" type="submit"><?= e(lang('youtube_disconnect')); ?></button>
            </form>
        <?php else: ?>
            <a class="button button-primary" href="<?= e(base_url('admin/youtube/connect.php')); ?>">
                <?= e(lang('youtube_connect')); ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="panel panel-spaced">
    <div>
        <h2><?= e(lang('youtube_how_it_works')); ?></h2>
        <p class="muted-text"><?= e(lang('youtube_how_it_works_body')); ?></p>
    </div>
</section>

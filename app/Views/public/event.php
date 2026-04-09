<?php
defined('LSB_APP') or exit;
?>
<article class="broadcast-page">
    <header class="broadcast-hero">
        <div class="broadcast-hero__content">
            <?php if ((int) $event['is_published'] !== 1): ?>
                <div class="alert alert-info">Mode prévisualisation d’une diffusion non publiée.</div>
            <?php endif; ?>
            <span class="badge <?= e($event['status_badge_class']); ?>"><?= e($event['status_label']); ?></span>
            <h1><?= e($event['title']); ?></h1>
            <?php if (!empty($event['description'])): ?>
                <p class="lede"><?= nl2br(e((string) $event['description'])); ?></p>
            <?php endif; ?>
            <div class="meta-row">
                <div class="meta-chip">
                    <span><?= e(lang('date_time')); ?></span>
                    <strong><?= e($event['display_start_at']); ?></strong>
                </div>
                <div class="meta-chip">
                    <span><?= e(lang('expected_duration')); ?></span>
                    <strong><?= e($event['duration_label']); ?></strong>
                </div>
            </div>

            <?php if ($event['effective_status'] === 'scheduled' && (bool) config('app.countdown_enabled', true)): ?>
                <div class="countdown-card">
                    <span><?= e(lang('countdown_label')); ?></span>
                    <strong data-countdown-target="<?= e($event['countdown_target']); ?>">--:--:--</strong>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <section class="section-block">
        <div class="section-block__header">
            <div>
                <p class="eyebrow"><?= e(lang('event_details')); ?></p>
                <h2><?= e($event['status_label']); ?></h2>
            </div>
        </div>

        <?php if (!empty($event['embed_url'])): ?>
            <div class="video-shell">
                <iframe
                    src="<?= e($event['embed_url']); ?>"
                    title="<?= e($event['title']); ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            </div>
        <?php else: ?>
            <div class="placeholder-card">
                <strong><?= e($event['status_label']); ?></strong>
                <p><?= e($event['player_placeholder']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($event['download_link'])): ?>
            <div class="button-row">
                <a class="button button-primary" href="<?= e($event['download_link']); ?>" target="_blank" rel="noopener">
                    <?= e(lang('download_broadcast')); ?>
                </a>
            </div>
        <?php endif; ?>
    </section>
</article>

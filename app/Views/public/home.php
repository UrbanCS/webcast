<?php
defined('LSB_APP') or exit;
?>
<section class="hero-panel">
    <div class="hero-panel__copy">
        <p class="eyebrow"><?= e(lang('events')); ?></p>
        <h1><?= e(lang('app_name')); ?></h1>
        <p class="lede"><?= e(lang('home_intro')); ?></p>
    </div>
</section>

<?php if ($featured !== null): ?>
    <section class="section-block">
        <div class="section-block__header">
            <div>
                <p class="eyebrow"><?= e($featured['effective_status'] === 'live' ? lang('current_card') : lang('schedule_card')); ?></p>
                <h2><?= e($featured['title']); ?></h2>
            </div>
            <span class="badge <?= e($featured['status_badge_class']); ?>"><?= e($featured['status_label']); ?></span>
        </div>

        <article class="feature-card">
            <div class="feature-card__meta">
                <div class="meta-chip">
                    <span><?= e(lang('date_time')); ?></span>
                    <strong><?= e($featured['display_start_at']); ?></strong>
                </div>
                <div class="meta-chip">
                    <span><?= e(lang('expected_duration')); ?></span>
                    <strong><?= e($featured['duration_label']); ?></strong>
                </div>
            </div>
            <p><?= nl2br(e((string) ($featured['description'] ?? ''))); ?></p>
            <div class="button-row">
                <a class="button button-primary" href="<?= e($featured['public_url']); ?>"><?= e(lang('view_event')); ?></a>
            </div>
        </article>
    </section>
<?php endif; ?>

<section class="section-block">
    <div class="section-block__header">
        <div>
            <p class="eyebrow"><?= e(lang('upcoming_broadcasts')); ?></p>
            <h2><?= e(lang('events')); ?></h2>
        </div>
    </div>

    <?php if (!empty($upcoming)): ?>
        <div class="card-grid">
            <?php foreach ($upcoming as $event): ?>
                <article class="event-card">
                    <span class="badge <?= e($event['status_badge_class']); ?>"><?= e($event['status_label']); ?></span>
                    <h3><a href="<?= e($event['public_url']); ?>"><?= e($event['title']); ?></a></h3>
                    <p><?= e($event['display_start_at']); ?></p>
                    <p><?= e($event['duration_label']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-card"><?= e(lang('no_upcoming_events')); ?></div>
    <?php endif; ?>
</section>

<section class="section-block">
    <div class="section-block__header">
        <div>
            <p class="eyebrow"><?= e(lang('archive_card')); ?></p>
            <h2><?= e(lang('archive')); ?></h2>
        </div>
    </div>

    <?php if (!empty($archive)): ?>
        <div class="card-grid">
            <?php foreach ($archive as $event): ?>
                <article class="event-card">
                    <span class="badge <?= e($event['status_badge_class']); ?>"><?= e($event['status_label']); ?></span>
                    <h3><a href="<?= e($event['public_url']); ?>"><?= e($event['title']); ?></a></h3>
                    <p><?= e($event['display_start_at']); ?></p>
                    <?php if (!empty($event['download_link'])): ?>
                        <a class="text-link" href="<?= e($event['download_link']); ?>" target="_blank" rel="noopener"><?= e(lang('download_broadcast')); ?></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-card"><?= e(lang('no_archive_events')); ?></div>
    <?php endif; ?>
</section>

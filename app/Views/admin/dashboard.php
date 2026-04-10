<?php
defined('LSB_APP') or exit;
?>
<section class="stats-grid">
    <article class="stat-card">
        <span><?= e(lang('stats_total')); ?></span>
        <strong><?= e((string) $stats['total']); ?></strong>
    </article>
    <article class="stat-card">
        <span><?= e(lang('stats_scheduled')); ?></span>
        <strong><?= e((string) $stats['scheduled']); ?></strong>
    </article>
    <article class="stat-card">
        <span><?= e(lang('stats_live')); ?></span>
        <strong><?= e((string) $stats['live']); ?></strong>
    </article>
    <article class="stat-card">
        <span><?= e(lang('stats_replay')); ?></span>
        <strong><?= e((string) $stats['replay']); ?></strong>
    </article>
    <article class="stat-card">
        <span><?= e(lang('stats_archived')); ?></span>
        <strong><?= e((string) $stats['archived']); ?></strong>
    </article>
</section>

<section class="panel">
    <form class="filters-grid" method="get" action="<?= e(base_url('admin.php')); ?>" data-filter-form>
        <label class="form-field">
            <span><?= e(lang('search')); ?></span>
            <input type="search" name="q" value="<?= e($query); ?>" placeholder="titre, slug, description">
        </label>

        <label class="form-field">
            <span><?= e(lang('filter_by_status')); ?></span>
            <select name="status" data-auto-submit>
                <option value=""><?= e(lang('all_statuses')); ?></option>
                <?php foreach (allowed_statuses() as $status): ?>
                    <option value="<?= e($status); ?>"<?= selected($statusFilter, $status); ?>><?= e(lang($status)); ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="filters-actions">
            <button class="button button-primary" type="submit"><?= e(lang('filters')); ?></button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?= e(lang('title')); ?></th>
                    <th><?= e(lang('status')); ?></th>
                    <th><?= e(lang('date_time')); ?></th>
                    <th><?= e(lang('duration_minutes')); ?></th>
                    <th><?= e(lang('published_label')); ?></th>
                    <th><?= e(lang('actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="6"><?= e(lang('no_events')); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <strong><?= e($event['title']); ?></strong>
                                <div class="muted-text"><?= e($event['slug']); ?></div>
                            </td>
                            <td><span class="badge <?= e($event['status_badge_class']); ?>"><?= e($event['status_label']); ?></span></td>
                            <td><?= e($event['display_start_at']); ?></td>
                            <td><?= e($event['duration_label']); ?></td>
                            <td><?= e((int) $event['is_published'] === 1 ? lang('published') : lang('unpublished')); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="button button-small" href="<?= e($event['preview_url']); ?>" target="_blank" rel="noopener"><?= e(lang('preview')); ?></a>
                                    <a class="button button-small" href="<?= e(base_url('admin/events/edit/?id=' . $event['id'])); ?>"><?= e(lang('edit')); ?></a>
                                    <button
                                        class="button button-small"
                                        type="button"
                                        data-copy-text="<?= e($event['public_url']); ?>"
                                    ><?= e(lang('copy_link')); ?></button>
                                    <form method="post" action="<?= e(base_url('admin/events/toggle-publish.php')); ?>">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token('publish_event_' . $event['id'])); ?>">
                                        <input type="hidden" name="id" value="<?= e((string) $event['id']); ?>">
                                        <input type="hidden" name="is_published" value="<?= e((string) $event['is_published']); ?>">
                                        <button class="button button-small" type="submit"><?= e((int) $event['is_published'] === 1 ? lang('unpublished') : lang('publish')); ?></button>
                                    </form>
                                    <form method="post" action="<?= e(base_url('admin/events/delete.php')); ?>" data-confirm="<?= e(lang('confirm_delete')); ?>">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token('delete_event_' . $event['id'])); ?>">
                                        <input type="hidden" name="id" value="<?= e((string) $event['id']); ?>">
                                        <button class="button button-danger button-small" type="submit"><?= e(lang('delete')); ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

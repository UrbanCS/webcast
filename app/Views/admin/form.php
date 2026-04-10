<?php
defined('LSB_APP') or exit;
?>
<section class="panel panel-spaced">
    <?php if (!empty($errors['form'])): ?>
        <div class="alert alert-error"><?= e($errors['form']); ?></div>
    <?php endif; ?>

    <?php if ($event !== null): ?>
        <div class="inline-actions">
            <a class="button" href="<?= e($event['preview_url']); ?>" target="_blank" rel="noopener"><?= e(lang('preview')); ?></a>
            <button class="button" type="button" data-copy-text="<?= e($event['public_url']); ?>"><?= e(lang('copy_link')); ?></button>
        </div>
    <?php endif; ?>

    <form class="admin-form admin-form--wide" method="post" action="<?= e($formAction); ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token('event_form')); ?>">

        <div class="form-grid">
            <label class="form-field">
                <span><?= e(lang('title')); ?></span>
                <input data-slug-source type="text" name="title" maxlength="190" required value="<?= e($formData['title']); ?>">
                <?php if (!empty($errors['title'])): ?><small class="form-error"><?= e($errors['title']); ?></small><?php endif; ?>
            </label>

            <label class="form-field">
                <span><?= e(lang('slug')); ?></span>
                <input data-slug-target type="text" name="slug" maxlength="190" value="<?= e($formData['slug']); ?>">
                <?php if (!empty($errors['slug'])): ?><small class="form-error"><?= e($errors['slug']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('description')); ?></span>
                <textarea name="description" rows="5"><?= e($formData['description']); ?></textarea>
            </label>

            <label class="form-field">
                <span><?= e(lang('date_time')); ?></span>
                <input type="datetime-local" name="start_at_local" required value="<?= e($formData['start_at_local']); ?>">
                <?php if (!empty($errors['start_at_local'])): ?><small class="form-error"><?= e($errors['start_at_local']); ?></small><?php endif; ?>
            </label>

            <label class="form-field">
                <span><?= e(lang('duration_minutes')); ?></span>
                <input type="number" name="duration_minutes" min="1" max="1440" required value="<?= e($formData['duration_minutes']); ?>">
                <?php if (!empty($errors['duration_minutes'])): ?><small class="form-error"><?= e($errors['duration_minutes']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('timezone')); ?></span>
                <select name="timezone" required>
                    <?php foreach ($timezones as $timezone): ?>
                        <option value="<?= e($timezone); ?>"<?= selected($formData['timezone'], $timezone); ?>><?= e($timezone); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['timezone'])): ?><small class="form-error"><?= e($errors['timezone']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('youtube_live')); ?></span>
                <input type="text" name="youtube_live_input" value="<?= e($formData['youtube_live_input']); ?>" placeholder="https://www.youtube.com/live/...">
                <small class="form-help"><?= e(lang('youtube_help')); ?></small>
                <?php if (!empty($errors['youtube_live_input'])): ?><small class="form-error"><?= e($errors['youtube_live_input']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('youtube_replay')); ?></span>
                <input type="text" name="youtube_replay_input" value="<?= e($formData['youtube_replay_input']); ?>" placeholder="https://youtu.be/...">
                <small class="form-help"><?= e(lang('youtube_help')); ?></small>
                <?php if (!empty($errors['youtube_replay_input'])): ?><small class="form-error"><?= e($errors['youtube_replay_input']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('download_url')); ?></span>
                <input type="text" name="download_url" value="<?= e($formData['download_url']); ?>" placeholder="https://votredomaine.com/downloads/video.mp4">
                <small class="form-help"><?= e(lang('download_help')); ?></small>
                <?php if (!empty($errors['download_url'])): ?><small class="form-error"><?= e($errors['download_url']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('local_file_path')); ?></span>
                <input type="text" name="local_file_path" value="<?= e($formData['local_file_path']); ?>" placeholder="20260409_abcd1234_replay.mp4">
                <small class="form-help"><?= e(lang('local_path_help')); ?></small>
                <?php if (!empty($errors['local_file_path'])): ?><small class="form-error"><?= e($errors['local_file_path']); ?></small><?php endif; ?>
            </label>

            <label class="form-field form-field--full">
                <span><?= e(lang('optional_upload')); ?></span>
                <input type="file" name="upload_file" accept=".mp4,.mov,.zip,.pdf">
                <small class="form-help"><?= e(lang('upload_help')); ?></small>
                <?php if (!empty($errors['upload_file'])): ?><small class="form-error"><?= e($errors['upload_file']); ?></small><?php endif; ?>
            </label>

            <label class="form-field">
                <span><?= e(lang('manual_override')); ?></span>
                <select name="manual_status">
                    <option value=""><?= e(lang('automatic_status')); ?></option>
                    <?php foreach (allowed_statuses() as $status): ?>
                        <option value="<?= e($status); ?>"<?= selected($formData['manual_status'], $status); ?>><?= e(lang($status)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-field checkbox-field">
                <input type="checkbox" name="is_published" value="1"<?= checked($formData['is_published']); ?>>
                <span><?= e(lang('publish')); ?></span>
            </label>
        </div>

        <div class="inline-actions form-actions">
            <button class="button button-primary" type="submit"><?= e(lang('save')); ?></button>
            <a class="button" href="<?= e(base_url('admin.php')); ?>"><?= e(lang('dashboard')); ?></a>
        </div>
    </form>

    <?php if ($event !== null): ?>
        <form method="post" action="<?= e(base_url('admin/events/delete.php')); ?>" data-confirm="<?= e(lang('confirm_delete')); ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token('delete_event_' . $event['id'])); ?>">
            <input type="hidden" name="id" value="<?= e((string) $event['id']); ?>">
            <button class="button button-danger" type="submit"><?= e(lang('delete')); ?></button>
        </form>
    <?php endif; ?>
</section>

<?php
defined('LSB_APP') or exit;
?>
<section class="auth-card">
    <p class="eyebrow"><?= e(lang('app_name')); ?></p>
    <h1><?= e(lang('admin_access')); ?></h1>

    <?php if (!empty($errors['form'])): ?>
        <div class="alert alert-error"><?= e($errors['form']); ?></div>
    <?php endif; ?>

    <?php if ($cmsMode): ?>
        <p><?= e(lang('cms_login_missing')); ?></p>
        <?php if ($loginUrl !== null): ?>
            <p><a class="button button-primary" href="<?= e($loginUrl); ?>"><?= e(lang('cms_login_button')); ?></a></p>
        <?php endif; ?>
    <?php else: ?>
        <form class="admin-form" method="post" action="<?= e(base_url('login.php')); ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token('admin_login')); ?>">

            <label class="form-field">
                <span><?= e(lang('email')); ?></span>
                <input type="email" name="email" required autocomplete="username" value="<?= e((string) ($_POST['email'] ?? '')); ?>">
            </label>

            <label class="form-field">
                <span><?= e(lang('password')); ?></span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>

            <button class="button button-primary button-block" type="submit"><?= e(lang('sign_in')); ?></button>
        </form>
        <p class="auth-note"><?= e(lang('standalone_credentials')); ?></p>
        <p class="auth-note"><?= e(lang('login_hint')); ?></p>
    <?php endif; ?>
</section>

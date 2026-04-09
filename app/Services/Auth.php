<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class Auth
{
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
    }

    public function mode(): string
    {
        return (string) \config('auth.mode', 'standalone');
    }

    public function isStandalone(): bool
    {
        return $this->mode() === 'standalone';
    }

    public function isAuthenticated(): bool
    {
        return match ($this->mode()) {
            'wordpress' => $this->wordpressAuthenticated(),
            'joomla' => $this->joomlaAuthenticated(),
            default => !empty($_SESSION['lsb_admin_authenticated']),
        };
    }

    public function guardAdmin(): void
    {
        if ($this->isAuthenticated()) {
            return;
        }

        if (!$this->isStandalone()) {
            $loginUrl = $this->cmsLoginUrl();
            if ($loginUrl !== null) {
                \redirect($loginUrl);
            }
        }

        \redirect(\base_url('login.php'));
    }

    public function attemptStandaloneLogin(string $email, string $password): bool
    {
        if (!$this->isStandalone()) {
            throw new RuntimeException('Le mode de connexion autonome est désactivé.');
        }

        $key = \client_ip() . '|' . strtolower(trim($email));
        $maxAttempts = (int) \config('auth.rate_limit_max_attempts', 5);
        $windowMinutes = (int) \config('auth.rate_limit_window_minutes', 15);

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts, $windowMinutes)) {
            Flash::add('error', \lang('too_many_attempts'));
            return false;
        }

        $expectedEmail = strtolower(trim((string) \config('auth.admin_email', '')));
        $hash = (string) \config('auth.admin_password_hash', '');

        if ($expectedEmail !== strtolower(trim($email)) || !password_verify($password, $hash)) {
            $this->rateLimiter->hit($key);
            return false;
        }

        $this->rateLimiter->clear($key);
        session_regenerate_id(true);
        $_SESSION['lsb_admin_authenticated'] = true;
        $_SESSION['lsb_admin_email'] = $expectedEmail;

        return true;
    }

    public function logout(): void
    {
        if ($this->mode() === 'wordpress' && ($logoutUrl = $this->cmsLogoutUrl()) !== null) {
            session_unset();
            session_destroy();
            \redirect($logoutUrl);
        }

        if ($this->mode() === 'joomla' && ($logoutUrl = $this->cmsLogoutUrl()) !== null) {
            session_unset();
            session_destroy();
            \redirect($logoutUrl);
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }

    public function cmsLoginUrl(): ?string
    {
        return match ($this->mode()) {
            'wordpress' => $this->wordpressLoginUrl(),
            'joomla' => $this->joomlaLoginUrl(),
            default => null,
        };
    }

    public function cmsLogoutUrl(): ?string
    {
        return match ($this->mode()) {
            'wordpress' => function_exists('wp_logout_url') ? wp_logout_url(\base_url('login.php')) : null,
            'joomla' => (string) \config('auth.joomla.login_url', '') !== '' ? (string) \config('auth.joomla.login_url', '') : null,
            default => null,
        };
    }

    private function wordpressAuthenticated(): bool
    {
        if (!$this->bootstrapWordPress()) {
            return false;
        }

        $capability = (string) \config('auth.wordpress.capability', 'manage_options');
        return function_exists('is_user_logged_in') && is_user_logged_in() && function_exists('current_user_can') && current_user_can($capability);
    }

    private function wordpressLoginUrl(): ?string
    {
        if (!$this->bootstrapWordPress()) {
            return null;
        }

        if (function_exists('wp_login_url')) {
            return wp_login_url(\base_url('admin.php'));
        }

        return null;
    }

    private function bootstrapWordPress(): bool
    {
        if (function_exists('is_user_logged_in')) {
            return true;
        }

        $bootstrap = (string) \config('auth.wordpress.bootstrap', '');
        if ($bootstrap !== '' && is_file($bootstrap)) {
            require_once $bootstrap;
        }

        return function_exists('is_user_logged_in');
    }

    private function joomlaAuthenticated(): bool
    {
        if (!$this->bootstrapJoomla()) {
            return false;
        }

        $factoryClass = '\\Joomla\\CMS\\Factory';
        if (!class_exists($factoryClass)) {
            return false;
        }

        $app = $factoryClass::getApplication();
        $user = method_exists($app, 'getIdentity') ? $app->getIdentity() : $factoryClass::getUser();

        if (!$user || empty($user->id)) {
            return false;
        }

        $groups = method_exists($user, 'getAuthorisedGroups') ? $user->getAuthorisedGroups() : ($user->groups ?? []);
        $allowedGroups = array_map('intval', (array) \config('auth.joomla.group_ids', [7, 8]));

        return count(array_intersect($allowedGroups, array_map('intval', (array) $groups))) > 0;
    }

    private function joomlaLoginUrl(): ?string
    {
        $configured = trim((string) \config('auth.joomla.login_url', ''));
        if ($configured !== '') {
            return $configured;
        }

        return null;
    }

    private function bootstrapJoomla(): bool
    {
        $factoryClass = '\\Joomla\\CMS\\Factory';
        if (class_exists($factoryClass)) {
            return true;
        }

        $root = rtrim((string) \config('auth.joomla.root_path', ''), '/');
        if ($root === '') {
            return false;
        }

        $defines = $root . '/defines.php';
        $framework = $root . '/includes/framework.php';

        if (!is_file($defines) || !is_file($framework)) {
            return false;
        }

        require_once $defines;
        require_once $framework;

        if (!class_exists($factoryClass)) {
            return false;
        }

        try {
            $app = $factoryClass::getApplication('site');
            if (method_exists($app, 'initialise')) {
                $app->initialise();
            }
        } catch (\Throwable $exception) {
            \log_message('Joomla bootstrap failed: ' . $exception->getMessage());
            return false;
        }

        return true;
    }
}

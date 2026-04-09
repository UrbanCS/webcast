<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Services\Flash;

if (!function_exists('bootstrap_app')) {
    function bootstrap_app(): void
    {
        $environment = (string) config('app.environment', 'production');
        $isProduction = $environment === 'production';

        error_reporting(E_ALL);
        ini_set('display_errors', $isProduction ? '0' : '1');
        ini_set('log_errors', '1');
        header_remove('X-Powered-By');

        date_default_timezone_set((string) config('app.default_timezone', 'UTC'));

        if ((bool) config('security.force_https', false) && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
            redirect('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'));
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $secure = (bool) config('security.secure_cookies', false);
            session_name((string) config('app.session_name', 'lifstories_broadcast'));
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => (string) config('security.same_site', 'Lax'),
            ]);
            session_start();
        }

        set_exception_handler(static function (Throwable $exception): void {
            log_message('Uncaught exception: ' . $exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
            http_response_code(500);

            if (is_production()) {
                echo 'Une erreur est survenue. Merci de réessayer plus tard.';
                return;
            }

            echo '<pre>' . e($exception->__toString()) . '</pre>';
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $GLOBALS['lsb_config'] ?? [];

        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('lang')) {
    function lang(string $key, array $replace = []): string
    {
        $messages = $GLOBALS['lsb_lang'] ?? [];
        $message = $messages[$key] ?? $key;

        foreach ($replace as $placeholder => $value) {
            $message = str_replace(':' . $placeholder, (string) $value, $message);
        }

        return $message;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_production')) {
    function is_production(): bool
    {
        return (string) config('app.environment', 'production') === 'production';
    }
}

if (!function_exists('log_message')) {
    function log_message(string $message): void
    {
        $directory = APP_ROOT . '/storage/logs';
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        @file_put_contents($directory . '/app.log', $line, FILE_APPEND);
    }
}

if (!function_exists('request_method')) {
    function request_method(): string
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return request_method() === 'POST';
    }
}

if (!function_exists('abort')) {
    function abort(int $statusCode = 404, string $message = ''): never
    {
        http_response_code($statusCode);
        echo $message !== '' ? e($message) : 'Erreur ' . $statusCode;
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('app_scheme')) {
    function app_scheme(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    }
}

if (!function_exists('app_host')) {
    function app_host(): string
    {
        return (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $configured = trim((string) config('app.base_url', ''));

        if ($configured !== '') {
            $path = parse_url($configured, PHP_URL_PATH);
            return rtrim((string) $path, '/');
        }

        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
        $appRoot = realpath(APP_ROOT);

        if ($documentRoot && $appRoot && str_starts_with($appRoot, $documentRoot)) {
            $relative = str_replace('\\', '/', substr($appRoot, strlen($documentRoot)));
            return rtrim($relative, '/');
        }

        return '';
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $configured = trim((string) config('app.base_url', ''));
        $base = $configured !== '' ? rtrim($configured, '/') : app_scheme() . '://' . app_host() . app_base_path();
        $normalizedPath = ltrim($path, '/');

        return $normalizedPath === '' ? $base : $base . '/' . $normalizedPath;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return base_url('public/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('current_url')) {
    function current_url(): string
    {
        return app_scheme() . '://' . app_host() . ($_SERVER['REQUEST_URI'] ?? '/');
    }
}

if (!function_exists('render')) {
    function render(string $view, array $data = [], string $layout = 'public'): void
    {
        $viewFile = APP_ROOT . '/app/Views/' . $view . '.php';
        $layoutFile = APP_ROOT . '/app/Views/layouts/' . $layout . '.php';

        if (!is_file($viewFile) || !is_file($layoutFile)) {
            abort(500, 'Vue introuvable.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}

if (!function_exists('render_partial')) {
    function render_partial(string $view, array $data = []): string
    {
        $viewFile = APP_ROOT . '/app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            return '';
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        return (string) ob_get_clean();
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = trim($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'diffusion-' . date('YmdHis');
    }
}

if (!function_exists('allowed_statuses')) {
    function allowed_statuses(): array
    {
        return ['scheduled', 'live', 'replay', 'archived'];
    }
}

if (!function_exists('selected')) {
    function selected(mixed $value, mixed $expected): string
    {
        return (string) $value === (string) $expected ? ' selected' : '';
    }
}

if (!function_exists('checked')) {
    function checked(mixed $value): string
    {
        return (bool) $value ? ' checked' : '';
    }
}

if (!function_exists('flash_messages')) {
    function flash_messages(): array
    {
        return Flash::pullAll();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(string $form = 'default'): string
    {
        return Csrf::token($form);
    }
}

if (!function_exists('client_ip')) {
    function client_ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', (string) $_SERVER[$key])[0]);
            }
        }

        return '0.0.0.0';
    }
}

if (!function_exists('format_minutes')) {
    function format_minutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($remaining === 0) {
            return $hours . ' h';
        }

        return $hours . ' h ' . $remaining . ' min';
    }
}

if (!function_exists('format_datetime_for_input')) {
    function format_datetime_for_input(?string $utcDateTime, string $timezone): string
    {
        if ($utcDateTime === null || $utcDateTime === '') {
            return '';
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $utcDateTime, new DateTimeZone('UTC'));
        if (!$date) {
            return '';
        }

        return $date->setTimezone(new DateTimeZone($timezone))->format('Y-m-d\TH:i');
    }
}

if (!function_exists('absolute_or_base_url')) {
    function absolute_or_base_url(string $value): string
    {
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return app_scheme() . '://' . app_host() . $value;
        }

        return base_url(ltrim($value, '/'));
    }
}

if (!function_exists('event_public_url')) {
    function event_public_url(array $event, bool $preview = false): string
    {
        $url = base_url('event.php?slug=' . urlencode((string) $event['slug']));
        if ($preview) {
            $url .= '&preview=1';
        }

        return $url;
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class(string $status): string
    {
        return match ($status) {
            'live' => 'badge-live',
            'replay' => 'badge-replay',
            'archived' => 'badge-archived',
            default => 'badge-scheduled',
        };
    }
}

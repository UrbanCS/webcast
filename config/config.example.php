<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Lifstories Broadcast',
        'environment' => 'production',
        'base_url' => '',
        'default_timezone' => 'America/Toronto',
        'session_name' => 'lifstories_broadcast',
        'max_upload_size_mb' => 250,
        'countdown_enabled' => true,
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'lifstories_broadcast',
        'username' => 'cpanel_db_user',
        'password' => 'change-this-password',
        'charset' => 'utf8mb4',
    ],
    'auth' => [
        'mode' => 'standalone',
        'admin_email' => 'admin@example.com',
        'admin_password_hash' => '$2y$10$123456789012345678901uxdGdOeBVGW767GxvC1Qbxml1IReoAxy',
        'rate_limit_max_attempts' => 5,
        'rate_limit_window_minutes' => 15,
        'wordpress' => [
            'bootstrap' => '',
            'capability' => 'manage_options',
        ],
        'joomla' => [
            'root_path' => '',
            'group_ids' => [7, 8],
            'login_url' => '',
        ],
    ],
    'security' => [
        'csrf_ttl' => 7200,
        'same_site' => 'Lax',
        'secure_cookies' => false,
        'force_https' => false,
    ],
];

<?php
declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class YoutubeLiveApiService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function isConnected(): bool
    {
        $token = $this->readToken();
        return !empty($token['access_token']) || !empty($token['refresh_token']);
    }

    public function redirectUri(): string
    {
        $configured = trim((string) \config('youtube.redirect_uri', ''));
        return $configured !== '' ? $configured : \base_url('admin/youtube/callback.php');
    }

    public function createAuthUrl(string $state): string
    {
        $this->assertConfigured();

        $query = http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes()),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTH_URL . '?' . $query;
    }

    public function exchangeCode(string $code): void
    {
        $this->assertConfigured();

        $response = $this->postForm(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        $this->saveToken($this->normalizeToken($response));
    }

    public function disconnect(): void
    {
        $file = $this->tokenFile();

        if (is_file($file)) {
            @unlink($file);
        }
    }

    public function channelInfo(): ?array
    {
        if (!$this->isConnected()) {
            return null;
        }

        $response = $this->apiRequest('GET', '/channels?part=snippet&mine=true');
        $item = $response['items'][0] ?? null;

        if (!is_array($item)) {
            return null;
        }

        $snippet = $item['snippet'] ?? [];

        return [
            'id' => (string) ($item['id'] ?? ''),
            'title' => (string) ($snippet['title'] ?? ''),
            'description' => (string) ($snippet['description'] ?? ''),
            'thumbnail' => (string) ($snippet['thumbnails']['default']['url'] ?? ''),
        ];
    }

    public function createLiveBroadcast(array $event): array
    {
        $this->assertConfigured();

        if (!$this->isConnected()) {
            throw new RuntimeException(\lang('youtube_not_connected'));
        }

        $startAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            (string) $event['start_at'],
            new DateTimeZone('UTC')
        );

        if (!$startAt) {
            throw new RuntimeException(\lang('validation_datetime'));
        }

        if ($startAt <= new DateTimeImmutable('now', new DateTimeZone('UTC'))) {
            throw new RuntimeException(\lang('youtube_start_future_required'));
        }

        $duration = max(1, (int) ($event['duration_minutes'] ?? 60));
        $endAt = $startAt->add(new DateInterval('PT' . $duration . 'M'));
        $privacyStatus = $this->defaultPrivacyStatus();

        $body = [
            'snippet' => [
                'title' => (string) $event['title'],
                'description' => (string) ($event['description'] ?? ''),
                'scheduledStartTime' => $startAt->format('Y-m-d\TH:i:s\Z'),
                'scheduledEndTime' => $endAt->format('Y-m-d\TH:i:s\Z'),
            ],
            'status' => [
                'privacyStatus' => $privacyStatus,
                'selfDeclaredMadeForKids' => false,
            ],
            'contentDetails' => [
                'enableDvr' => true,
                'enableEmbed' => true,
                'recordFromStart' => true,
            ],
        ];

        $response = $this->apiRequest('POST', '/liveBroadcasts?part=snippet,status,contentDetails', $body);
        $videoId = (string) ($response['id'] ?? '');

        if ($videoId === '') {
            throw new RuntimeException(\lang('youtube_create_failed'));
        }

        return [
            'video_id' => $videoId,
            'watch_url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'privacy_status' => $privacyStatus,
            'raw' => $response,
        ];
    }

    private function apiRequest(string $method, string $path, ?array $jsonBody = null): array
    {
        $accessToken = $this->accessToken();
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ];

        $body = null;
        if ($jsonBody !== null) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $this->sendRequest($method, self::API_BASE . $path, $headers, $body);
    }

    private function accessToken(): string
    {
        $token = $this->readToken();

        if (empty($token)) {
            throw new RuntimeException(\lang('youtube_not_connected'));
        }

        $expiresAt = (int) ($token['expires_at'] ?? 0);
        if (!empty($token['access_token']) && $expiresAt > time() + 60) {
            return (string) $token['access_token'];
        }

        if (empty($token['refresh_token'])) {
            throw new RuntimeException(\lang('youtube_reconnect_required'));
        }

        $response = $this->postForm(self::TOKEN_URL, [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => (string) $token['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        $refreshed = array_merge($token, $this->normalizeToken($response));
        $this->saveToken($refreshed);

        return (string) $refreshed['access_token'];
    }

    private function postForm(string $url, array $fields): array
    {
        return $this->sendRequest(
            'POST',
            $url,
            [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            http_build_query($fields, '', '&', PHP_QUERY_RFC3986)
        );
    }

    private function sendRequest(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => 30,
            ]);

            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new RuntimeException($error !== '' ? $error : \lang('youtube_api_error'));
            }

            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseBody = substr((string) $response, $headerSize);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body ?? '',
                    'ignore_errors' => true,
                    'timeout' => 30,
                ],
            ]);
            $responseBody = file_get_contents($url, false, $context);
            $statusLine = $http_response_header[0] ?? '';
            preg_match('/\s(\d{3})\s/', $statusLine, $matches);
            $status = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        $decoded = json_decode((string) $responseBody, true);
        $data = is_array($decoded) ? $decoded : [];

        if ($status < 200 || $status >= 300) {
            $message = (string) ($data['error']['message'] ?? $data['error_description'] ?? \lang('youtube_api_error'));
            throw new RuntimeException($message);
        }

        return $data;
    }

    private function normalizeToken(array $token): array
    {
        if (empty($token['access_token'])) {
            throw new RuntimeException((string) ($token['error_description'] ?? \lang('youtube_api_error')));
        }

        $expiresIn = max(60, (int) ($token['expires_in'] ?? 3600));
        $token['expires_at'] = time() + $expiresIn;
        $token['saved_at'] = time();

        return $token;
    }

    private function readToken(): array
    {
        $file = $this->tokenFile();

        if (!is_file($file)) {
            return [];
        }

        $contents = file_get_contents($file);
        $decoded = json_decode((string) $contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveToken(array $token): void
    {
        $file = $this->tokenFile();
        $directory = dirname($file);

        if (!is_dir($directory)) {
            @mkdir($directory, 0750, true);
        }

        file_put_contents($file, json_encode($token, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        @chmod($file, 0600);
    }

    private function tokenFile(): string
    {
        $path = trim((string) \config('youtube.token_path', 'storage/youtube/oauth-token.json'));

        if ($path === '') {
            $path = 'storage/youtube/oauth-token.json';
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return APP_ROOT . '/' . ltrim($path, '/');
    }

    private function clientId(): string
    {
        return trim((string) \config('youtube.client_id', ''));
    }

    private function clientSecret(): string
    {
        return trim((string) \config('youtube.client_secret', ''));
    }

    private function scopes(): array
    {
        return ['https://www.googleapis.com/auth/youtube'];
    }

    private function defaultPrivacyStatus(): string
    {
        $status = trim((string) \config('youtube.default_privacy_status', 'unlisted'));
        return in_array($status, ['private', 'unlisted', 'public'], true) ? $status : 'unlisted';
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException(\lang('youtube_not_configured'));
        }
    }
}

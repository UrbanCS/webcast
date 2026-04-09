<?php
declare(strict_types=1);

namespace App\Services;

class YoutubeService
{
    public function normalize(?string $input): array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return [
                'input' => null,
                'video_id' => null,
            ];
        }

        $videoId = $this->extractVideoId($input);

        return [
            'input' => $input,
            'video_id' => $videoId,
        ];
    }

    public function extractVideoId(string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input) === 1) {
            return $input;
        }

        $parts = parse_url($input);

        if ($parts === false) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($host === 'youtu.be') {
            $segments = explode('/', $path);
            return $this->validateId($segments[0] ?? '');
        }

        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com')) {
            parse_str((string) ($parts['query'] ?? ''), $query);

            if (!empty($query['v'])) {
                return $this->validateId((string) $query['v']);
            }

            $segments = explode('/', $path);

            foreach ($segments as $index => $segment) {
                if (in_array($segment, ['embed', 'live', 'shorts', 'watch'], true)) {
                    return $this->validateId($segments[$index + 1] ?? '');
                }
            }
        }

        return null;
    }

    public function embedUrl(?string $videoId): ?string
    {
        if ($videoId === null || $videoId === '') {
            return null;
        }

        return 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0';
    }

    private function validateId(string $value): ?string
    {
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $value) === 1 ? $value : null;
    }
}

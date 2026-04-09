<?php
declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

class EventStatusService
{
    public function compute(array $event, ?DateTimeImmutable $now = null): string
    {
        $manualStatus = (string) ($event['manual_status'] ?? '');
        if ($manualStatus !== '' && in_array($manualStatus, \allowed_statuses(), true)) {
            return $manualStatus;
        }

        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $start = $this->startUtc($event);
        $end = $start->modify('+' . max(1, (int) ($event['duration_minutes'] ?? 60)) . ' minutes');

        if ($now < $start) {
            return 'scheduled';
        }

        if ($now >= $start && $now < $end) {
            return 'live';
        }

        if (!empty($event['youtube_replay_video_id'])) {
            return 'replay';
        }

        return 'archived';
    }

    public function startUtc(array $event): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $event['start_at'], new DateTimeZone('UTC'));
        return $date ?: new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function endUtc(array $event): DateTimeImmutable
    {
        return $this->startUtc($event)->modify('+' . max(1, (int) ($event['duration_minutes'] ?? 60)) . ' minutes');
    }

    public function displayDate(array $event, string $format = 'd/m/Y H:i'): string
    {
        $timezone = (string) ($event['timezone'] ?? \config('app.default_timezone', 'UTC'));
        return $this->startUtc($event)->setTimezone(new DateTimeZone($timezone))->format($format);
    }

    public function inputDate(array $event): string
    {
        $timezone = (string) ($event['timezone'] ?? \config('app.default_timezone', 'UTC'));
        return \format_datetime_for_input((string) $event['start_at'], $timezone);
    }

    public function countdownTarget(array $event): string
    {
        return $this->startUtc($event)->format(DATE_ATOM);
    }
}

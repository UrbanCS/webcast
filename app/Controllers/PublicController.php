<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\BroadcastEvent;
use App\Services\Auth;
use App\Services\EventStatusService;
use App\Services\UploadService;
use App\Services\YoutubeService;

class PublicController
{
    private BroadcastEvent $events;
    private EventStatusService $statusService;
    private YoutubeService $youtubeService;
    private Auth $auth;
    private UploadService $uploadService;

    public function __construct()
    {
        $this->events = new BroadcastEvent();
        $this->statusService = new EventStatusService();
        $this->youtubeService = new YoutubeService();
        $this->auth = new Auth();
        $this->uploadService = new UploadService();
    }

    public function home(): void
    {
        $events = array_map(fn (array $event): array => $this->decorate($event), $this->events->allPublished());

        usort($events, static fn (array $a, array $b): int => strcmp($a['start_at'], $b['start_at']));

        $featured = null;
        $upcoming = [];
        $archive = [];

        foreach ($events as $event) {
            if ($event['effective_status'] === 'live' && $featured === null) {
                $featured = $event;
                continue;
            }

            if ($event['effective_status'] === 'scheduled') {
                if ($featured === null) {
                    $featured = $event;
                    continue;
                }

                $upcoming[] = $event;
                continue;
            }

            $archive[] = $event;
        }

        usort($archive, static fn (array $a, array $b): int => strcmp($b['start_at'], $a['start_at']));

        \render('public/home', [
            'pageTitle' => \lang('app_name'),
            'featured' => $featured,
            'upcoming' => $upcoming,
            'archive' => $archive,
            'embedMode' => $this->embedMode(),
        ], $this->layout());
    }

    public function show(): void
    {
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $preview = isset($_GET['preview']) && $_GET['preview'] === '1' && $this->auth->isAuthenticated();

        if ($slug === '') {
            \redirect(\base_url());
        }

        $event = $preview ? $this->events->findBySlug($slug) : $this->events->findPublishedBySlug($slug);

        if ($event === null) {
            http_response_code(404);
            \render('public/not-found', [
                'pageTitle' => \lang('event_not_found'),
                'embedMode' => $this->embedMode(),
            ], $this->layout());
            return;
        }

        $event = $this->decorate($event);

        \render('public/event', [
            'pageTitle' => $event['title'],
            'event' => $event,
            'embedMode' => $this->embedMode(),
        ], $this->layout());
    }

    private function decorate(array $event): array
    {
        $status = $this->statusService->compute($event);
        $replayId = (string) ($event['youtube_replay_video_id'] ?? '');
        $liveId = (string) ($event['youtube_live_video_id'] ?? '');
        $playerVideoId = null;
        $playerType = null;
        $placeholder = \lang('placeholder_scheduled');

        if ($status === 'live') {
            $playerVideoId = $liveId !== '' ? $liveId : null;
            $playerType = $playerVideoId ? 'live' : null;
            $placeholder = $playerVideoId ? '' : \lang('placeholder_no_live');
        } elseif ($replayId !== '') {
            $playerVideoId = $replayId;
            $playerType = 'replay';
            $placeholder = '';
        } elseif ($status === 'scheduled') {
            $placeholder = \lang('placeholder_scheduled');
        } else {
            $placeholder = \lang('placeholder_no_replay');
        }

        $downloadLink = null;
        $resolvedLocalPath = !empty($event['local_file_path'])
            ? $this->uploadService->resolveLocalPath((string) $event['local_file_path'])
            : null;

        if ($resolvedLocalPath !== null && is_file($resolvedLocalPath)) {
            $downloadLink = \base_url('download.php?slug=' . urlencode((string) $event['slug']));
        } elseif (!empty($event['download_url'])) {
            $downloadLink = \absolute_or_base_url((string) $event['download_url']);
        }

        $event['effective_status'] = $status;
        $event['status_label'] = \lang($status);
        $event['status_badge_class'] = \status_badge_class($status);
        $event['display_start_at'] = $this->statusService->displayDate($event) . ' (' . $event['timezone'] . ')';
        $event['countdown_target'] = $this->statusService->countdownTarget($event);
        $event['duration_label'] = \format_minutes((int) $event['duration_minutes']);
        $event['player_video_id'] = $playerVideoId;
        $event['player_type'] = $playerType;
        $event['embed_url'] = $this->youtubeService->embedUrl($playerVideoId);
        $event['player_placeholder'] = $placeholder;
        $event['download_link'] = $downloadLink;
        $event['public_url'] = \event_public_url($event);
        $event['preview_url'] = \event_public_url($event, true);

        return $event;
    }

    private function embedMode(): bool
    {
        return isset($_GET['embed']) && $_GET['embed'] === '1';
    }

    private function layout(): string
    {
        return $this->embedMode() ? 'embed' : 'public';
    }
}

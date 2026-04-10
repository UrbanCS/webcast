<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\BroadcastEvent;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\EventStatusService;
use App\Services\Flash;
use App\Services\UploadService;
use App\Services\YoutubeService;
use DateTimeImmutable;
use DateTimeZone;

class AdminController
{
    private BroadcastEvent $events;
    private Auth $auth;
    private EventStatusService $statusService;
    private YoutubeService $youtubeService;
    private UploadService $uploadService;

    public function __construct()
    {
        $this->events = new BroadcastEvent();
        $this->auth = new Auth();
        $this->statusService = new EventStatusService();
        $this->youtubeService = new YoutubeService();
        $this->uploadService = new UploadService();
    }

    public function login(): void
    {
        if ($this->auth->isAuthenticated()) {
            \redirect(\base_url('admin.php'));
        }

        if (!$this->auth->isStandalone()) {
            $loginUrl = $this->auth->cmsLoginUrl();

            if ($loginUrl !== null) {
                \redirect($loginUrl);
            }

            \render('admin/login', [
                'pageTitle' => \lang('login'),
                'cmsMode' => true,
                'loginUrl' => null,
                'errors' => [],
            ], 'auth');
            return;
        }

        $errors = [];

        if (\is_post()) {
            if (!Csrf::validate('admin_login', $_POST['_csrf'] ?? null)) {
                $errors['form'] = \lang('validation_csrf');
            } else {
                $email = trim((string) ($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');

                if (!$this->auth->attemptStandaloneLogin($email, $password)) {
                    $errors['form'] = \lang('invalid_credentials');
                } else {
                    Flash::add('success', \lang('admin_access'));
                    \redirect(\base_url('admin.php'));
                }
            }
        }

        \render('admin/login', [
            'pageTitle' => \lang('login'),
            'cmsMode' => false,
            'loginUrl' => null,
            'errors' => $errors,
        ], 'auth');
    }

    public function logout(): void
    {
        $this->auth->logout();
        \redirect(\base_url('login.php'));
    }

    public function dashboard(): void
    {
        $this->auth->guardAdmin();

        $query = trim((string) ($_GET['q'] ?? ''));
        $statusFilter = $this->normalizeStatusFilter((string) ($_GET['status'] ?? ''));
        $events = array_map(fn (array $event): array => $this->decorateEvent($event), $this->events->search($query));

        $stats = [
            'total' => count($events),
            'scheduled' => 0,
            'live' => 0,
            'replay' => 0,
            'archived' => 0,
        ];

        foreach ($events as $event) {
            $stats[$event['effective_status']]++;
        }

        if ($statusFilter !== '' && in_array($statusFilter, \allowed_statuses(), true)) {
            $events = array_values(array_filter(
                $events,
                static fn (array $event): bool => $event['effective_status'] === $statusFilter
            ));
        }

        \render('admin/dashboard', [
            'pageTitle' => \lang('dashboard'),
            'events' => $events,
            'stats' => $stats,
            'query' => $query,
            'statusFilter' => $statusFilter,
            'authMode' => $this->auth->mode(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->auth->guardAdmin();

        $errors = [];
        $formData = $this->defaultFormData();

        if (\is_post()) {
            [$payload, $errors, $formData] = $this->buildPayload();

            if ($errors === []) {
                $eventId = $this->events->create($payload);
                Flash::add('success', \lang('save_success_create'));
                \redirect(\base_url('admin/events/edit/?id=' . $eventId));
            }
        }

        \render('admin/form', [
            'pageTitle' => \lang('create_event'),
            'mode' => 'create',
            'formAction' => \base_url('admin/events/create/'),
            'errors' => $errors,
            'formData' => $formData,
            'event' => null,
            'timezones' => timezone_identifiers_list(),
        ], 'admin');
    }

    public function edit(int $id): void
    {
        $this->auth->guardAdmin();

        $event = $this->events->findById($id);
        if ($event === null) {
            \abort(404, \lang('event_not_found'));
        }

        $errors = [];
        $formData = $this->formDataFromEvent($event);

        if (\is_post()) {
            [$payload, $errors, $formData] = $this->buildPayload($id, $event);

            if ($errors === []) {
                $this->events->update($id, $payload);
                Flash::add('success', \lang('save_success_update'));
                \redirect(\base_url('admin/events/edit/?id=' . $id));
            }
        }

        $event = $this->decorateEvent($event);

        \render('admin/form', [
            'pageTitle' => \lang('edit_event'),
            'mode' => 'edit',
            'formAction' => \base_url('admin/events/edit/?id=' . $id),
            'errors' => $errors,
            'formData' => $formData,
            'event' => $event,
            'timezones' => timezone_identifiers_list(),
        ], 'admin');
    }

    public function delete(int $id): void
    {
        $this->auth->guardAdmin();

        if (!Csrf::validate('delete_event_' . $id, $_POST['_csrf'] ?? null)) {
            Flash::add('error', \lang('validation_csrf'));
            \redirect(\base_url('admin.php'));
        }

        $this->events->delete($id);
        Flash::add('success', \lang('delete_success'));
        \redirect(\base_url('admin.php'));
    }

    public function togglePublish(int $id): void
    {
        $this->auth->guardAdmin();

        if (!Csrf::validate('publish_event_' . $id, $_POST['_csrf'] ?? null)) {
            Flash::add('error', \lang('validation_csrf'));
            \redirect(\base_url('admin.php'));
        }

        $event = $this->events->findById($id);
        if ($event === null) {
            \abort(404, \lang('event_not_found'));
        }

        $published = isset($_POST['is_published']) && (int) $_POST['is_published'] === 1;
        $this->events->setPublished($id, !$published);
        Flash::add('success', $published ? \lang('unpublish_success') : \lang('publish_success'));
        \redirect(\base_url('admin.php'));
    }

    private function buildPayload(?int $excludeId = null, ?array $existing = null): array
    {
        $errors = [];
        $formData = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'start_at_local' => trim((string) ($_POST['start_at_local'] ?? '')),
            'duration_minutes' => trim((string) ($_POST['duration_minutes'] ?? '')),
            'timezone' => trim((string) ($_POST['timezone'] ?? \config('app.default_timezone', 'UTC'))),
            'youtube_live_input' => trim((string) ($_POST['youtube_live_input'] ?? '')),
            'youtube_replay_input' => trim((string) ($_POST['youtube_replay_input'] ?? '')),
            'download_url' => trim((string) ($_POST['download_url'] ?? '')),
            'local_file_path' => trim((string) ($_POST['local_file_path'] ?? '')),
            'manual_status' => trim((string) ($_POST['manual_status'] ?? '')),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        if (!Csrf::validate('event_form', $_POST['_csrf'] ?? null)) {
            $errors['form'] = \lang('validation_csrf');
        }

        if ($formData['title'] === '') {
            $errors['title'] = \lang('validation_title');
        }

        $formData['slug'] = $formData['slug'] !== '' ? \slugify($formData['slug']) : \slugify($formData['title']);
        if ($formData['slug'] === '' || $this->events->slugExists($formData['slug'], $excludeId)) {
            $errors['slug'] = \lang('validation_slug');
        }

        if (!in_array($formData['timezone'], timezone_identifiers_list(), true)) {
            $errors['timezone'] = \lang('validation_timezone');
        }

        $startAtUtc = null;
        if ($formData['start_at_local'] === '') {
            $errors['start_at_local'] = \lang('validation_datetime');
        } else {
            $date = DateTimeImmutable::createFromFormat(
                'Y-m-d\TH:i',
                $formData['start_at_local'],
                new DateTimeZone($formData['timezone'] ?: 'UTC')
            );

            if (!$date) {
                $errors['start_at_local'] = \lang('validation_datetime');
            } else {
                $startAtUtc = $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }
        }

        $duration = filter_var(
            $formData['duration_minutes'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 1440]]
        );

        if ($duration === false) {
            $errors['duration_minutes'] = \lang('validation_duration');
        }

        $live = $this->youtubeService->normalize($formData['youtube_live_input']);
        if ($formData['youtube_live_input'] !== '' && $live['video_id'] === null) {
            $errors['youtube_live_input'] = \lang('validation_youtube');
        }

        $replay = $this->youtubeService->normalize($formData['youtube_replay_input']);
        if ($formData['youtube_replay_input'] !== '' && $replay['video_id'] === null) {
            $errors['youtube_replay_input'] = \lang('validation_youtube');
        }

        if ($formData['download_url'] !== '' && !$this->validDownloadUrl($formData['download_url'])) {
            $errors['download_url'] = \lang('validation_download_url');
        }

        $localFilePath = null;
        if ($formData['local_file_path'] !== '') {
            $localFilePath = $this->uploadService->sanitizeRelativePath($formData['local_file_path']);
            if ($localFilePath === null) {
                $errors['local_file_path'] = \lang('validation_local_path');
            }
        }

        if (
            $errors === []
            && isset($_FILES['upload_file'])
            && (int) ($_FILES['upload_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
        ) {
            try {
                $uploaded = $this->uploadService->upload($_FILES['upload_file']);
                $localFilePath = $uploaded['relative_path'];
                $formData['local_file_path'] = $localFilePath;
            } catch (\RuntimeException $exception) {
                $errors['upload_file'] = $exception->getMessage();
            }
        }

        $manualStatus = $formData['manual_status'];
        if ($manualStatus !== '' && !in_array($manualStatus, \allowed_statuses(), true)) {
            $manualStatus = null;
        }

        $payload = [
            'title' => $formData['title'],
            'slug' => $formData['slug'],
            'description' => $formData['description'] !== '' ? $formData['description'] : null,
            'start_at' => $startAtUtc,
            'duration_minutes' => $duration !== false ? (int) $duration : 60,
            'timezone' => $formData['timezone'],
            'youtube_live_input' => $live['input'],
            'youtube_live_video_id' => $live['video_id'],
            'youtube_replay_input' => $replay['input'],
            'youtube_replay_video_id' => $replay['video_id'],
            'download_url' => $formData['download_url'] !== '' ? $formData['download_url'] : null,
            'local_file_path' => $localFilePath,
            'manual_status' => $manualStatus,
            'is_published' => $formData['is_published'],
        ];

        return [$payload, $errors, $formData];
    }

    private function decorateEvent(array $event): array
    {
        $status = $this->statusService->compute($event);
        $event['effective_status'] = $status;
        $event['status_label'] = \lang($status);
        $event['status_badge_class'] = \status_badge_class($status);
        $event['display_start_at'] = $this->statusService->displayDate($event) . ' (' . $event['timezone'] . ')';
        $event['duration_label'] = \format_minutes((int) $event['duration_minutes']);
        $event['public_url'] = \event_public_url($event);
        $event['preview_url'] = \event_public_url($event, true);
        return $event;
    }

    private function defaultFormData(): array
    {
        $defaultDate = new DateTimeImmutable('now', new DateTimeZone((string) \config('app.default_timezone', 'UTC')));

        return [
            'title' => '',
            'slug' => '',
            'description' => '',
            'start_at_local' => $defaultDate->modify('+1 day')->format('Y-m-d\TH:i'),
            'duration_minutes' => '60',
            'timezone' => (string) \config('app.default_timezone', 'UTC'),
            'youtube_live_input' => '',
            'youtube_replay_input' => '',
            'download_url' => '',
            'local_file_path' => '',
            'manual_status' => '',
            'is_published' => 1,
        ];
    }

    private function formDataFromEvent(array $event): array
    {
        return [
            'title' => (string) $event['title'],
            'slug' => (string) $event['slug'],
            'description' => (string) ($event['description'] ?? ''),
            'start_at_local' => $this->statusService->inputDate($event),
            'duration_minutes' => (string) $event['duration_minutes'],
            'timezone' => (string) $event['timezone'],
            'youtube_live_input' => (string) ($event['youtube_live_input'] ?? ''),
            'youtube_replay_input' => (string) ($event['youtube_replay_input'] ?? ''),
            'download_url' => (string) ($event['download_url'] ?? ''),
            'local_file_path' => (string) ($event['local_file_path'] ?? ''),
            'manual_status' => (string) ($event['manual_status'] ?? ''),
            'is_published' => (int) $event['is_published'],
        ];
    }

    private function validDownloadUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }

        return str_starts_with($url, '/');
    }

    private function normalizeStatusFilter(string $status): string
    {
        $status = trim(strtolower($status));

        if ($status === '') {
            return '';
        }

        if (in_array($status, \allowed_statuses(), true)) {
            return $status;
        }

        $translations = [
            'diffusion prévue' => 'scheduled',
            'en direct' => 'live',
            'rediffusion disponible' => 'replay',
            'archivée' => 'archived',
            'archivee' => 'archived',
        ];

        return $translations[$status] ?? '';
    }
}

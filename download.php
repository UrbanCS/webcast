<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Models\BroadcastEvent;
use App\Services\Auth;
use App\Services\UploadService;

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    abort(404);
}

$model = new BroadcastEvent();
$auth = new Auth();
$event = $model->findPublishedBySlug($slug);

if ($event === null && $auth->isAuthenticated()) {
    $event = $model->findBySlug($slug);
}

if ($event === null || (empty($event['local_file_path']) && empty($event['download_url']))) {
    abort(404);
}

if (!$auth->isAuthenticated() && !empty($event['access_code_hash']) && empty($_SESSION['lsb_event_access'][(int) $event['id']])) {
    abort(403, lang('access_required_message'));
}

$uploadService = new UploadService();
$absolutePath = !empty($event['local_file_path'])
    ? $uploadService->resolveLocalPath((string) $event['local_file_path'])
    : null;

if ($absolutePath !== null && is_file($absolutePath)) {
    $uploadService->sendFile($absolutePath, basename($absolutePath));
}

if (!empty($event['download_url'])) {
    redirect(absolute_or_base_url((string) $event['download_url']));
}

abort(404);

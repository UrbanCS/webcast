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

if ($event === null || empty($event['local_file_path'])) {
    abort(404);
}

$uploadService = new UploadService();
$absolutePath = $uploadService->resolveLocalPath((string) $event['local_file_path']);

if ($absolutePath === null || !is_file($absolutePath)) {
    abort(404);
}

$uploadService->sendFile($absolutePath, basename($absolutePath));

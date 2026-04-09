<?php
declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    abort(404, lang('event_not_found'));
}

$controller = new App\Controllers\AdminController();
$controller->edit((int) $id);

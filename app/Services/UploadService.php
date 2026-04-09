<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class UploadService
{
    private array $allowedExtensions = ['mp4', 'mov', 'zip', 'pdf'];

    public function sanitizeRelativePath(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_contains($path, '../') || str_contains($path, '..\\')) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            return null;
        }

        return preg_replace('/[^A-Za-z0-9_\/\.-]/', '', $path);
    }

    public function resolveLocalPath(string $relativePath): ?string
    {
        $sanitized = $this->sanitizeRelativePath($relativePath);
        if ($sanitized === null) {
            return null;
        }

        $fullPath = APP_ROOT . '/storage/uploads/' . $sanitized;
        $directory = realpath(dirname($fullPath));
        $uploadsRoot = realpath(APP_ROOT . '/storage/uploads');

        if ($directory === false || $uploadsRoot === false || !str_starts_with($directory, $uploadsRoot)) {
            return null;
        }

        return $fullPath;
    }

    public function upload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException(\lang('upload_error'));
        }

        $originalName = (string) ($file['name'] ?? 'download.bin');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new RuntimeException(\lang('upload_invalid_extension'));
        }

        $maxBytes = (int) \config('app.max_upload_size_mb', 250) * 1024 * 1024;
        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException(\lang('upload_too_large'));
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, (string) $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeMap = [
            'mp4' => ['video/mp4', 'application/octet-stream'],
            'mov' => ['video/quicktime', 'application/octet-stream'],
            'zip' => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
            'pdf' => ['application/pdf', 'application/octet-stream'],
        ];

        if ($mime !== null && !in_array($mime, $allowedMimeMap[$extension], true)) {
            throw new RuntimeException(\lang('upload_invalid_extension'));
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '-', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'fichier';
        $targetName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '_' . $safeName . '.' . $extension;
        $targetDirectory = APP_ROOT . '/storage/uploads';

        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException(\lang('upload_error'));
        }

        $targetPath = $targetDirectory . '/' . $targetName;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new RuntimeException(\lang('upload_error'));
        }

        return [
            'relative_path' => $targetName,
            'absolute_path' => $targetPath,
        ];
    }

    public function sendFile(string $absolutePath, ?string $downloadName = null): never
    {
        $downloadName ??= basename($absolutePath);
        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($absolutePath);
        exit;
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\Database;
use PDO;

class BroadcastEvent
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function allPublished(): array
    {
        $statement = $this->db->query('SELECT * FROM broadcast_events WHERE is_published = 1 ORDER BY start_at DESC');
        return $statement->fetchAll();
    }

    public function search(string $query = ''): array
    {
        if ($query === '') {
            $statement = $this->db->query('SELECT * FROM broadcast_events ORDER BY start_at DESC');
            return $statement->fetchAll();
        }

        $statement = $this->db->prepare(
            'SELECT * FROM broadcast_events
             WHERE title LIKE :title_query
                OR slug LIKE :slug_query
                OR description LIKE :description_query
             ORDER BY start_at DESC'
        );
        $wildcardQuery = '%' . $query . '%';
        $statement->execute([
            'title_query' => $wildcardQuery,
            'slug_query' => $wildcardQuery,
            'description_query' => $wildcardQuery,
        ]);
        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM broadcast_events WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $event = $statement->fetch();
        return $event ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM broadcast_events WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $event = $statement->fetch();
        return $event ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM broadcast_events WHERE slug = :slug AND is_published = 1 LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $event = $statement->fetch();
        return $event ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM broadcast_events WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }

    public function create(array $payload): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO broadcast_events (
                title, slug, description, start_at, duration_minutes, timezone,
                youtube_live_input, youtube_live_video_id, youtube_replay_input, youtube_replay_video_id,
                download_url, local_file_path, manual_status, is_published, created_at, updated_at
            ) VALUES (
                :title, :slug, :description, :start_at, :duration_minutes, :timezone,
                :youtube_live_input, :youtube_live_video_id, :youtube_replay_input, :youtube_replay_video_id,
                :download_url, :local_file_path, :manual_status, :is_published, NOW(), NOW()
            )'
        );
        $statement->execute($payload);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): bool
    {
        $payload['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE broadcast_events SET
                title = :title,
                slug = :slug,
                description = :description,
                start_at = :start_at,
                duration_minutes = :duration_minutes,
                timezone = :timezone,
                youtube_live_input = :youtube_live_input,
                youtube_live_video_id = :youtube_live_video_id,
                youtube_replay_input = :youtube_replay_input,
                youtube_replay_video_id = :youtube_replay_video_id,
                download_url = :download_url,
                local_file_path = :local_file_path,
                manual_status = :manual_status,
                is_published = :is_published,
                updated_at = NOW()
            WHERE id = :id'
        );

        return $statement->execute($payload);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM broadcast_events WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public function setPublished(int $id, bool $published): bool
    {
        $statement = $this->db->prepare('UPDATE broadcast_events SET is_published = :published, updated_at = NOW() WHERE id = :id');
        return $statement->execute([
            'id' => $id,
            'published' => $published ? 1 : 0,
        ]);
    }
}

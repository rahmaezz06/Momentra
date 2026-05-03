<?php

class LikeModel extends Model {

    public function toggle(int $userId, int $postId): array {
        $existing = $this->fetchOne("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?", 'ii', [$userId, $postId]);
        if ($existing) {
            $this->query("DELETE FROM likes WHERE user_id = ? AND post_id = ?", 'ii', [$userId, $postId]);
            $action = 'unliked';
        } else {
            $this->query("INSERT INTO likes (user_id, post_id) VALUES (?, ?)", 'ii', [$userId, $postId]);
            $action = 'liked';
        }
        $count = $this->fetchScalar("SELECT COUNT(*) FROM likes WHERE post_id = ?", 'i', [$postId]);
        return ['action' => $action, 'count' => (int) $count];
    }

    public function getCount(int $postId): int {
        return (int) $this->fetchScalar("SELECT COUNT(*) FROM likes WHERE post_id = ?", 'i', [$postId]);
    }

    public function getLikers(int $postId): array {
        return $this->fetchAll("
            SELECT u.id, u.username, u.avatar
            FROM likes l JOIN users u ON l.user_id = u.id
            WHERE l.post_id = ?
        ", 'i', [$postId]);
    }
}

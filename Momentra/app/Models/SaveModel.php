<?php

class SaveModel extends Model {

    public function toggle(int $userId, int $postId): array {
        $existing = $this->fetchOne("SELECT 1 FROM saved_posts WHERE user_id = ? AND post_id = ?", 'ii', [$userId, $postId]);
        if ($existing) {
            $this->query("DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?", 'ii', [$userId, $postId]);
            return ['action' => 'unsaved'];
        } else {
            $this->query("INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)", 'ii', [$userId, $postId]);
            return ['action' => 'saved'];
        }
    }

    public function getSaved(int $userId): array {
        return $this->fetchAll("
            SELECT p.*, u.username, u.avatar,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND (parent_id = 0 OR parent_id IS NULL)) as comment_count
            FROM saved_posts sp
            JOIN posts p ON sp.post_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE sp.user_id = ?
            ORDER BY sp.id DESC
        ", 'i', [$userId]);
    }
}

<?php

class CommentModel extends Model {

    public function getWithReplies(int $postId, int $userId): array {
        $comments = $this->fetchAll("
            SELECT c.*, u.username, u.avatar, u.id as user_id,
                   (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
                   (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as replies_count,
                   EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ? AND (c.parent_id = 0 OR c.parent_id IS NULL)
            ORDER BY c.created_at ASC
        ", 'ii', [$userId, $postId]);

        foreach ($comments as &$comment) {
            $comment['replies'] = $this->fetchAll("
                SELECT c.*, u.username, u.avatar, u.id as user_id,
                       (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
                       EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.parent_id = ?
                ORDER BY c.created_at ASC
            ", 'ii', [$userId, $comment['id']]);
        }
        return $comments;
    }

    public function getReplies(int $commentId, int $userId): array {
        return $this->fetchAll("
            SELECT c.*, u.username, u.avatar,
                   (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
                   EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.parent_id = ?
            ORDER BY c.created_at ASC
        ", 'ii', [$userId, $commentId]);
    }

    public function add(int $userId, int $postId, string $body, int $parentId = 0): array {
        $this->query(
            "INSERT INTO comments (user_id, post_id, body, parent_id) VALUES (?, ?, ?, ?)",
            'iisi', [$userId, $postId, $body, $parentId]
        );
        $commentId = $this->lastId();
        $comment = $this->fetchOne("
            SELECT c.*, u.username, u.avatar, u.id as user_id
            FROM comments c JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ", 'i', [$commentId]);

        $comment['avatar_url']    = avatarUrl($comment['avatar']);
        $comment['likes_count']   = 0;
        $comment['replies_count'] = 0;
        $comment['user_liked']    = false;
        $comment['replies']       = [];
        return $comment;
    }

    public function delete(int $commentId, int $userId, int $postOwnerId = 0): bool {
        // صاحب الكومنت أو صاحب البوست يقدر يمسح
        $comment = $this->fetchOne("SELECT user_id, post_id FROM comments WHERE id = ?", 'i', [$commentId]);
        if (!$comment) return false;
        if ($comment['user_id'] !== $userId && $postOwnerId !== $userId) return false;
        $this->query("DELETE FROM comments WHERE parent_id = ?", 'i', [$commentId]);
        $this->query("DELETE FROM comments WHERE id = ?", 'i', [$commentId]);
        return true;
    }

    public function toggleLike(int $userId, int $commentId): array {
        $existing = $this->fetchOne("SELECT 1 FROM comment_likes WHERE user_id = ? AND comment_id = ?", 'ii', [$userId, $commentId]);
        if ($existing) {
            $this->query("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?", 'ii', [$userId, $commentId]);
            $action = 'unliked';
        } else {
            $this->query("INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())", 'ii', [$userId, $commentId]);
            $action = 'liked';
        }
        $count = $this->fetchScalar("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?", 'i', [$commentId]);
        return ['action' => $action, 'count' => (int) $count];
    }
}

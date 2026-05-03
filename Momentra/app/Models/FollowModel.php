<?php

class FollowModel extends Model {

    public function toggle(int $followerId, int $followingId): array {
        if ($followerId === $followingId) return ['action' => 'error'];

        $existing = $this->fetchOne("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?", 'ii', [$followerId, $followingId]);
        if ($existing) {
            $this->query("DELETE FROM follows WHERE follower_id = ? AND following_id = ?", 'ii', [$followerId, $followingId]);
            $action = 'unfollowed';
        } else {
            $this->query("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)", 'ii', [$followerId, $followingId]);
            $action = 'followed';
        }
        return ['action' => $action];
    }

    public function isFollowing(int $followerId, int $followingId): bool {
        return (bool) $this->fetchOne("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?", 'ii', [$followerId, $followingId]);
    }

    public function getFollowerCount(int $userId): int {
        return (int) $this->fetchScalar("SELECT COUNT(*) FROM follows WHERE following_id = ?", 'i', [$userId]);
    }

    public function getFollowingCount(int $userId): int {
        return (int) $this->fetchScalar("SELECT COUNT(*) FROM follows WHERE follower_id = ?", 'i', [$userId]);
    }

    public function getFollowers(int $userId, int $viewerId): array {
        return $this->fetchAll("
            SELECT u.id, u.username, u.full_name, u.avatar,
                   EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = u.id) as viewer_follows
            FROM follows f JOIN users u ON f.follower_id = u.id
            WHERE f.following_id = ?
            ORDER BY f.created_at DESC
        ", 'ii', [$viewerId, $userId]);
    }

    public function getFollowing(int $userId, int $viewerId): array {
        return $this->fetchAll("
            SELECT u.id, u.username, u.full_name, u.avatar,
                   EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = u.id) as viewer_follows
            FROM follows f JOIN users u ON f.following_id = u.id
            WHERE f.follower_id = ?
            ORDER BY f.created_at DESC
        ", 'ii', [$viewerId, $userId]);
    }
}

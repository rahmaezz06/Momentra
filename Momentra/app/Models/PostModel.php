<?php

class PostModel extends Model {

    public function getFeedPosts(int $userId): array {
        return $this->fetchAll("
            SELECT p.*, u.username, u.avatar,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                   EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked,
                   EXISTS(SELECT 1 FROM saved_posts WHERE post_id = p.id AND user_id = ?) as user_saved
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id IN (
                SELECT following_id FROM follows WHERE follower_id = ?
                UNION SELECT ?
            )
            ORDER BY p.created_at DESC
        ", 'iiii', [$userId, $userId, $userId, $userId]);
    }

    public function getById(int $postId): ?array {
        return $this->fetchOne("
            SELECT p.*, u.username, u.avatar, u.full_name,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ", 'i', [$postId]);
    }

    public function getByUser(int $userId): array {
        return $this->fetchAll("
            SELECT p.*,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ", 'i', [$userId]);
    }

    public function create(int $userId, string $caption, string $location, $file, string $postType = 'photo', string $textBg = 'gradient-purple'): array {
        $errors   = [];
        $filename = null;

        if ($postType === 'photo') {
            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'errors' => ['Please select an image.']];
            }
            $finfo       = finfo_open(FILEINFO_MIME_TYPE);
            $mime        = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowedMimes = array_merge(ALLOWED_TYPES, ['image/jpg', 'image/jfif', 'image/pjpeg']);

            if (!in_array($mime, $allowedMimes)) {
                return ['success' => false, 'errors' => ['Invalid file type. Allowed: JPG, PNG, GIF, WEBP.']];
            }
            if ($file['size'] > MAX_FILE_SIZE) {
                return ['success' => false, 'errors' => ['File too large. Max 5MB.']];
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jfif', 'jpe'])) $ext = 'jpg';

            $postsDir = UPLOAD_PATH . 'posts/';
            if (!is_dir($postsDir)) mkdir($postsDir, 0755, true);

            $filename = uniqid() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $postsDir . $filename)) {
                return ['success' => false, 'errors' => ['Failed to upload image.']];
            }
        } else {
            if (!trim($caption)) {
                return ['success' => false, 'errors' => ['Please write something for your post.']];
            }
        }

        $this->query(
            "INSERT INTO posts (user_id, image, caption, location, post_type, text_bg) VALUES (?, ?, ?, ?, ?, ?)",
            'isssss',
            [$userId, $filename, $caption, $location, $postType, $textBg]
        );
        return ['success' => true, 'post_id' => $this->lastId()];
    }

    public function update(int $postId, int $userId, string $caption, string $location): bool {
        $this->query(
            "UPDATE posts SET caption = ?, location = ? WHERE id = ? AND user_id = ?",
            'ssii', [$caption, $location, $postId, $userId]
        );
        return true;
    }

    public function delete(int $postId, int $userId): bool {
        $post = $this->fetchOne("SELECT image FROM posts WHERE id = ? AND user_id = ?", 'ii', [$postId, $userId]);
        if (!$post) return false;
        if (!empty($post['image'])) {
            $path = UPLOAD_PATH . 'posts/' . $post['image'];
            if (file_exists($path)) unlink($path);
        }
        $this->query("DELETE FROM posts WHERE id = ? AND user_id = ?", 'ii', [$postId, $userId]);
        return true;
    }
}

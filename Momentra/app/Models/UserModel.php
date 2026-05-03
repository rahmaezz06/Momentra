<?php

class UserModel extends Model {

    public function getByUsername(string $username): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE username = ?", 's', [$username]);
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE id = ?", 'i', [$id]);
    }

    public function updateProfile(int $userId, array $data, $avatarFile = null): array {
        $errors = [];

        if (isset($data['username'])) {
            $existing = $this->fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", 'si', [$data['username'], $userId]);
            if ($existing) $errors[] = 'Username already taken.';
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $finfo        = finfo_open(FILEINFO_MIME_TYPE);
            $mime         = finfo_file($finfo, $avatarFile['tmp_name']);
            finfo_close($finfo);
            $allowedMimes = array_merge(ALLOWED_TYPES, ['image/jpg', 'image/jfif', 'image/pjpeg']);

            if (in_array($mime, $allowedMimes) && $avatarFile['size'] <= MAX_FILE_SIZE) {
                $ext = strtolower(pathinfo($avatarFile['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jfif', 'jpe'])) $ext = 'jpg';

                $avatarsDir = UPLOAD_PATH . 'avatars/';
                if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0755, true);

                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($avatarFile['tmp_name'], $avatarsDir . $filename)) {
                    $old = $this->fetchOne("SELECT avatar FROM users WHERE id = ?", 'i', [$userId]);
                    if ($old['avatar'] && $old['avatar'] !== 'default.png') {
                        $oldPath = UPLOAD_PATH . 'avatars/' . $old['avatar'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $this->query("UPDATE users SET avatar = ? WHERE id = ?", 'si', [$filename, $userId]);
                }
            }
        }

        $this->query(
            "UPDATE users SET username = ?, full_name = ?, bio = ?, website = ? WHERE id = ?",
            'ssssi',
            [$data['username'] ?? '', $data['full_name'] ?? '', $data['bio'] ?? '', $data['website'] ?? '', $userId]
        );
        return ['success' => true, 'errors' => []];
    }

    public function search(string $query, int $limit = 8): array {
        $like  = "%$query%";
        $users = $this->fetchAll("
            SELECT id, username, full_name, avatar
            FROM users WHERE username LIKE ? OR full_name LIKE ?
            LIMIT ?
        ", 'ssi', [$like, $like, $limit]);

        foreach ($users as &$u) {
            $u['avatar_url'] = avatarUrl($u['avatar']);
        }
        return $users;
    }
}

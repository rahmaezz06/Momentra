<?php
// ============================================================
//  Helper Functions
// ============================================================

function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function avatarUrl($avatar) {
    if (!$avatar || $avatar === 'default.png') {
        return BASE_URL . '/includes/default-avatar.png';
    }
    return BASE_URL . '/uploads/avatars/' . $avatar;
}

function postImageUrl($image) {
    return BASE_URL . '/uploads/posts/' . $image;
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

// ============================================================
//  Post Functions
// ============================================================

function getFeedPosts($userId) {
    $db = db();
    $stmt = $db->prepare("
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
    ");
    $stmt->execute([$userId, $userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function getPost($postId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT p.*, u.username, u.avatar, u.full_name,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$postId]);
    return $stmt->fetch();
}

function getUserPosts($userId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
        FROM posts p
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function createPost($userId, $caption, $location, $file, $postType = 'photo', $textBg = 'gradient-purple') {
    $errors = [];
    $filename = null;

    if ($postType === 'photo') {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Please select an image.';
            return ['success' => false, 'errors' => $errors];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Accept jfif as jpeg
        $allowedMimes = array_merge(ALLOWED_TYPES, ['image/jpg', 'image/jfif', 'image/pjpeg']);
        if (!in_array($mime, $allowedMimes)) {
            $errors[] = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP.';
            return ['success' => false, 'errors' => $errors];
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File too large. Max 5MB.';
            return ['success' => false, 'errors' => $errors];
        }

        // Use .jpg for jfif files
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jfif', 'jpe'])) $ext = 'jpg';

        // Make sure upload directories exist
        $postsDir = UPLOAD_PATH . 'posts/';
        if (!is_dir($postsDir)) mkdir($postsDir, 0755, true);

        $filename   = uniqid() . '.' . $ext;
        $uploadPath = $postsDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $errors[] = 'Failed to upload image. Check that the uploads/posts folder exists and is writable.';
            return ['success' => false, 'errors' => $errors];
        }
    } else {
        // Text-only post — caption is required
        if (!trim($caption)) {
            $errors[] = 'Please write something for your post.';
            return ['success' => false, 'errors' => $errors];
        }
    }

    $db = db();
    $stmt = $db->prepare("INSERT INTO posts (user_id, image, caption, location, post_type, text_bg) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $filename, $caption, $location, $postType, $textBg]);

    return ['success' => true, 'post_id' => $db->lastInsertId()];
}

function updatePost($postId, $userId, $caption, $location) {
    $db = db();
    $stmt = $db->prepare("UPDATE posts SET caption = ?, location = ? WHERE id = ? AND user_id = ?");
    return $stmt->execute([$caption, $location, $postId, $userId]);
}

function deletePost($postId, $userId) {
    $db = db();
    // Get image filename first
    $stmt = $db->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $post = $stmt->fetch();
    
    if ($post) {
        // Delete image file only if post has an image (not text-only)
        if (!empty($post['image'])) {
            $filePath = UPLOAD_PATH . 'posts/' . $post['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Delete from database (cascade will delete likes, comments, saves)
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        return $stmt->execute([$postId, $userId]);
    }
    return false;
}

// ============================================================
//  Like Functions
// ============================================================

function toggleLike($userId, $postId) {
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$userId, $postId]);
    
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?")->execute([$userId, $postId]);
        $action = 'unliked';
    } else {
        $db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")->execute([$userId, $postId]);
        $action = 'liked';
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    $count = $stmt->fetchColumn();
    
    return ['action' => $action, 'count' => $count];
}

// ============================================================
//  Save Functions
// ============================================================

function toggleSave($userId, $postId) {
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM saved_posts WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$userId, $postId]);
    
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?")->execute([$userId, $postId]);
        return ['action' => 'unsaved'];
    } else {
        $db->prepare("INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)")->execute([$userId, $postId]);
        return ['action' => 'saved'];
    }
}

// ============================================================
//  Comment Functions
// ============================================================

function getComments($postId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.avatar 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ? 
        AND (c.parent_id = 0 OR c.parent_id IS NULL)
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function getCommentReplies($commentId, $userId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.avatar,
               (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
               EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.parent_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$userId, $commentId]);
    return $stmt->fetchAll();
}

// الدالة المهمة التي كانت ناقصة - تجلب التعليقات مع الردود
function getPostCommentsWithReplies($postId, $userId) {
    $db = db();
    
    // First get all parent comments (parent_id = 0 or NULL)
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.avatar, u.id as user_id,
               (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
               (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as replies_count,
               EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ? 
        AND (c.parent_id = 0 OR c.parent_id IS NULL)
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$userId, $postId]);
    $comments = $stmt->fetchAll();
    
    // For each comment, load its replies
    foreach ($comments as &$comment) {
        $stmt = $db->prepare("
            SELECT c.*, u.username, u.avatar, u.id as user_id,
                   (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count,
                   EXISTS(SELECT 1 FROM comment_likes WHERE comment_id = c.id AND user_id = ?) as user_liked
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.parent_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$userId, $comment['id']]);
        $comment['replies'] = $stmt->fetchAll();
    }
    
    return $comments;
}

function addComment($userId, $postId, $body, $parentId = 0) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO comments (user_id, post_id, body, parent_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $postId, $body, $parentId]);
    
    $commentId = $db->lastInsertId();
    
    // Get the inserted comment with user info
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.avatar, u.id as user_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    $comment['avatar_url']    = avatarUrl($comment['avatar']);
    $comment['likes_count']   = 0;
    $comment['replies_count'] = 0;
    $comment['user_liked']    = false;
    $comment['replies']       = [];
    
    return $comment;
}

function deleteComment($commentId, $userId) {
    $db = db();
    // First delete all replies to this comment
    $stmt = $db->prepare("DELETE FROM comments WHERE parent_id = ?");
    $stmt->execute([$commentId]);
    
    // Then delete the comment itself
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    return $stmt->execute([$commentId, $userId]);
}

// ============================================================
//  Comment Like Functions
// ============================================================

function likeComment($userId, $commentId) {
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $stmt->execute([$userId, $commentId]);
    
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?")->execute([$userId, $commentId]);
        return true;
    } else {
        $db->prepare("INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())")->execute([$userId, $commentId]);
        return true;
    }
}

function getCommentLikesCount($commentId) {
    $db = db();
    $stmt = $db->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
    $stmt->execute([$commentId]);
    return $stmt->fetchColumn();
}

function userLikedComment($userId, $commentId) {
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $stmt->execute([$userId, $commentId]);
    return $stmt->fetch() ? true : false;
}

// ============================================================
//  Follow Functions
// ============================================================

function toggleFollow($followerId, $followingId) {
    if ($followerId == $followingId) return ['action' => 'error'];
    
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$followerId, $followingId]);
    
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?")->execute([$followerId, $followingId]);
        $action = 'unfollowed';
    } else {
        $db->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)")->execute([$followerId, $followingId]);
        $action = 'followed';
    }
    
    return ['action' => $action];
}

function getFollowerCount($userId) {
    $db = db();
    $stmt = $db->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getFollowingCount($userId) {
    $db = db();
    $stmt = $db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function isFollowing($followerId, $followingId) {
    $db = db();
    $stmt = $db->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$followerId, $followingId]);
    return $stmt->fetch() ? true : false;
}

// ============================================================
//  Profile Functions
// ============================================================

function getUserByUsername($username) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function updateProfile($userId, $data, $avatarFile = null) {
    $errors = [];
    $db = db();
    
    // Check username uniqueness
    if (isset($data['username'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken.';
        }
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Update avatar if provided
    if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $avatarFile['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = array_merge(ALLOWED_TYPES, ['image/jpg', 'image/jfif', 'image/pjpeg']);
        if (in_array($mime, $allowedMimes) && $avatarFile['size'] <= MAX_FILE_SIZE) {
            $ext = strtolower(pathinfo($avatarFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jfif', 'jpe'])) $ext = 'jpg';

            $avatarsDir = UPLOAD_PATH . 'avatars/';
            if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0755, true);

            $filename   = uniqid() . '.' . $ext;
            $uploadPath = $avatarsDir . $filename;

            if (move_uploaded_file($avatarFile['tmp_name'], $uploadPath)) {
                // Delete old avatar if not default
                $stmt = $db->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $old = $stmt->fetch();
                if ($old['avatar'] && $old['avatar'] !== 'default.png') {
                    $oldPath = UPLOAD_PATH . 'avatars/' . $old['avatar'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$filename, $userId]);
            }
        }
    }
    
    // Update other fields
    $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, bio = ?, website = ? WHERE id = ?");
    $stmt->execute([
        $data['username'] ?? '',
        $data['full_name'] ?? '',
        $data['bio'] ?? '',
        $data['website'] ?? '',
        $userId
    ]);
    
    return ['success' => true, 'errors' => []];
}

// ============================================================
//  Search Functions
// ============================================================

function searchUsers($query, $limit = 8) {
    $db = db();
    $stmt = $db->prepare("
        SELECT id, username, full_name, avatar 
        FROM users 
        WHERE username LIKE ? OR full_name LIKE ?
        LIMIT ?
    ");
    $stmt->bindValue(1, "%$query%", PDO::PARAM_STR);
    $stmt->bindValue(2, "%$query%", PDO::PARAM_STR);
    $stmt->bindValue(3, (int)$limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
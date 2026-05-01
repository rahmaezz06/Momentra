<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }
verifyCsrf();

$userId   = $_SESSION['user_id'];
$password = $_POST['password'] ?? '';

// Verify password before deleting
$stmt = db()->prepare('SELECT password_hash, avatar FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
    exit;
}

try {
    $db = db();
    $db->beginTransaction();

    // 1. Delete post image files from disk
    $stmt = $db->prepare('SELECT image FROM posts WHERE user_id = ? AND image IS NOT NULL AND image != ""');
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll() as $post) {
        $path = UPLOAD_PATH . 'posts/' . $post['image'];
        if (file_exists($path)) unlink($path);
    }

    // 2. Delete avatar file from disk
    if ($user['avatar'] && $user['avatar'] !== 'default.png') {
        $path = UPLOAD_PATH . 'avatars/' . $user['avatar'];
        if (file_exists($path)) unlink($path);
    }

    // 3. Delete DB rows in correct order (child tables first)
    // Comment likes on this user's comments
    $db->prepare('DELETE cl FROM comment_likes cl
                  JOIN comments c ON cl.comment_id = c.id
                  WHERE c.user_id = ?')->execute([$userId]);

    // Comment likes made BY this user
    $db->prepare('DELETE FROM comment_likes WHERE user_id = ?')->execute([$userId]);

    // Likes on this user's posts
    $db->prepare('DELETE l FROM likes l
                  JOIN posts p ON l.post_id = p.id
                  WHERE p.user_id = ?')->execute([$userId]);

    // Likes made BY this user
    $db->prepare('DELETE FROM likes WHERE user_id = ?')->execute([$userId]);

    // Saves on this user's posts
    $db->prepare('DELETE sp FROM saved_posts sp
                  JOIN posts p ON sp.post_id = p.id
                  WHERE p.user_id = ?')->execute([$userId]);

    // Saves made BY this user
    $db->prepare('DELETE FROM saved_posts WHERE user_id = ?')->execute([$userId]);

    // Comments on this user's posts
    $db->prepare('DELETE c FROM comments c
                  JOIN posts p ON c.post_id = p.id
                  WHERE p.user_id = ?')->execute([$userId]);

    // Comments made BY this user
    $db->prepare('DELETE FROM comments WHERE user_id = ?')->execute([$userId]);

    // Follows
    $db->prepare('DELETE FROM follows WHERE follower_id = ? OR following_id = ?')->execute([$userId, $userId]);

    // Password resets
    $db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$userId]);

    // Posts
    $db->prepare('DELETE FROM posts WHERE user_id = ?')->execute([$userId]);

    // Finally delete the user
    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

    $db->commit();

    // Destroy session & cookies
    $_SESSION = [];
    session_destroy();
    setcookie('remember_user', '', time() - 3600, '/');

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => 'Could not delete account. Please try again.']);
}

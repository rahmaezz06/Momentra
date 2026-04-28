<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
startSession();
if (!isLoggedIn()) { 
    echo json_encode(['error' => 'Unauthorized']); 
    exit; 
}

$postId = (int)($_GET['post_id'] ?? 0);
if (!$postId) { 
    echo json_encode(['error' => 'Invalid post']); 
    exit; 
}

$stmt = db()->prepare("
    SELECT u.username, u.avatar, u.full_name
    FROM likes l
    JOIN users u ON l.user_id = u.id
    WHERE l.post_id = ?
    ORDER BY l.created_at DESC
    LIMIT 500
");
$stmt->execute([$postId]);
$likers = $stmt->fetchAll();

foreach ($likers as &$u) {
    $u['avatar_url'] = avatarUrl($u['avatar']);
}

echo json_encode([
    'success' => true, 
    'likers' => $likers
]);

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

verifyCsrf();

$commentId = (int)($_POST['comment_id'] ?? 0);

if (!$commentId) {
    echo json_encode([
        'success' => false, 
        'error' => 'Missing comment ID'
    ]);
    exit;
}

$result = likeComment($_SESSION['user_id'], $commentId);
$likesCount = getCommentLikesCount($commentId);
$userLiked = userLikedComment($_SESSION['user_id'], $commentId);

echo json_encode([
    'success' => $result,
    'likes_count' => $likesCount,
    'user_liked' => $userLiked
]);
?>
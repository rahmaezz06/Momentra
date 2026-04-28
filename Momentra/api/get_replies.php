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

$commentId = (int)($_GET['comment_id'] ?? 0);

if (!$commentId) {
    echo json_encode([
        'success' => false, 
        'error' => 'Missing comment ID'
    ]);
    exit;
}

$replies = getCommentReplies($commentId, $_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'replies' => $replies
]);
?>
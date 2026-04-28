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

$postId = (int)($_POST['post_id'] ?? 0);
$body   = trim($_POST['body'] ?? '');
$parentId = (int)($_POST['parent_id'] ?? 0); // For replies

if (!$postId || !$body) { 
    echo json_encode(['success' => false, 'error' => 'Missing fields']); 
    exit; 
}

$comment = addComment($_SESSION['user_id'], $postId, $body, $parentId);

if ($comment) {
    echo json_encode(['success' => true, 'comment' => $comment]);
} else {
    echo json_encode(['success' => false, 'error' => 'Could not add comment']);
}
?>
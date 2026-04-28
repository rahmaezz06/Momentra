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
if (!$postId) { 
    echo json_encode(['success' => false, 'error' => 'Invalid post']); 
    exit; 
}
$ok = deletePost($postId, $_SESSION['user_id']);
echo json_encode([
    'success' => $ok, 'error' => $ok ? null : 'Not found or not authorized'
]);

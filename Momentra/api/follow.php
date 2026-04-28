<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
startSession();
if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }
verifyCsrf();
$followingId = (int)($_POST['following_id'] ?? 0);
if (!$followingId) {
     echo json_encode(['error' => 'Invalid user']); 
     exit;
}
$result = toggleFollow($_SESSION['user_id'], $followingId);
$followerCount = getFollowerCount($followingId);
echo json_encode([
    'action' => $result['action'],
     'follower_count' => $followerCount
]);

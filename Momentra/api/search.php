<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
startSession();
if (!isLoggedIn()) { 
    echo json_encode([]); 
    exit; 
}
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 1) { 
    echo json_encode([]); 
    exit; }
$users = searchUsers($q, 8);
$result = array_map(function($u) {
    $u['avatar_url'] = avatarUrl($u['avatar']);
    return $u;
}, $users);
echo json_encode($result);

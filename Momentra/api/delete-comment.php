<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
startSession();
if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }
verifyCsrf();

$commentId = (int)($_POST['comment_id'] ?? 0);
if (!$commentId) { echo json_encode(['success' => false]); exit; }

$userId = $_SESSION['user_id'];

// Check if user owns the comment OR owns the post
$stmt = db()->prepare('
    SELECT c.id FROM comments c
    JOIN posts p ON c.post_id = p.id
    WHERE c.id = ? AND (c.user_id = ? OR p.user_id = ?)
');
$stmt->execute([$commentId, $userId, $userId]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$ok = deleteComment($commentId, $userId);

// If post owner deleted someone else's comment, force delete
if (!$ok) {
    $db = db();
    $db->prepare('DELETE FROM comments WHERE parent_id = ?')->execute([$commentId]);
    $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
    $ok = $stmt->execute([$commentId]);
}

echo json_encode(['success' => (bool)$ok]);

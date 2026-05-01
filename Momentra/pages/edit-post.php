<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$me     = currentUser();
$postId = (int)($_GET['id'] ?? 0);
$post   = getPost($postId);
if (!$post || $post['user_id'] !== $me['id']) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ok = updatePost($postId, $me['id'], $_POST['caption'] ?? '', $_POST['location'] ?? '');
    if ($ok) {
        header('Location: ' . BASE_URL . '/pages/post.php?id=' . $postId); exit;
    }
    $errors[] = 'Update failed. Please try again.';
}

$pageTitle = 'Edit Post';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">Edit Post</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <img src="<?= postImageUrl($post['image']) ?>" alt="Post" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px;margin-bottom:20px;">

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="form-group">
      <label>Caption</label>
      <textarea name="caption"><?= h($post['caption']) ?></textarea>
    </div>
    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" value="<?= h($post['location']) ?>">
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $postId ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

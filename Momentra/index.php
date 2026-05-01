<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

startSession();

// Hard redirect if not logged in — no HTML before this
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    header('Connection: close');
    exit;
}

$me    = currentUser();
$posts = getFeedPosts($me['id']);

$pageTitle = 'Feed';
require_once __DIR__ . '/includes/header.php';
?>

<main>

<?php if (empty($posts)): ?>
<div class="card" style="padding:48px 24px;text-align:center;">
  <div style="font-size:3rem;margin-bottom:16px;">📸</div>
  <h2 style="margin-bottom:8px;font-family:'Playfair Display',serif;">Your feed is empty</h2>
  <p style="color:var(--muted);margin-bottom:24px;">Follow people or share your first photo to get started.</p>
  <a href="<?= BASE_URL ?>/pages/create.php" class="btn btn-primary">Share a Photo</a>
</div>
<?php else: ?>

<?php
$bgMap = [
  'gradient-purple' => 'linear-gradient(135deg,#6366f1,#8b5cf6)',
  'gradient-pink'   => 'linear-gradient(135deg,#ec4899,#f43f5e)',
  'gradient-blue'   => 'linear-gradient(135deg,#3b82f6,#06b6d4)',
  'gradient-green'  => 'linear-gradient(135deg,#10b981,#84cc16)',
  'gradient-orange' => 'linear-gradient(135deg,#f59e0b,#ef4444)',
  'dark'            => '#111827',
  'light'           => '#f1f5f9',
];
?>

<?php foreach ($posts as $post): ?>
<!-- POST CARD -->
<article class="card post-card" id="post-<?= $post['id'] ?>">

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;">
    <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($post['username']) ?>" 
    style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
      <img src="<?= avatarUrl($post['avatar']) ?>" 
      class="avatar <?= $post['user_id'] !== $me['id'] ? 'avatar-ring' : '' ?>" 
      width="40" height="40" >
      <div>
        <div style="font-weight:600;font-size:.9rem;"><?= h($post['username']) ?></div>
        <?php if ($post['location']): ?>
        <div style="font-size:.75rem;color:var(--muted);">📍 <?= h($post['location']) ?></div>
        <?php endif; ?>
      </div>
    </a>
    <?php if ($post['user_id'] === $me['id']): ?>
    <div style="position:relative;">
      <button class="btn btn-ghost" onclick="toggleMenu(<?= $post['id'] ?>)">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
      </button>
      <div id="menu-<?= $post['id'] ?>" 
      style="display:none;position:absolute;right:0;top:36px;background:var(--card);border:1px solid var(--border);border-radius:10px;min-width:140px;z-index:10;box-shadow:0 4px 20px #00000060;overflow:hidden;">
        <a href="<?= BASE_URL ?>/pages/edit-post.php?id=<?= $post['id'] ?>" 
        style="display:block;padding:10px 16px;text-decoration:none;color:var(--text);font-size:.875rem;">✏️ Edit</a>
        <button onclick="deletePost(<?= $post['id'] ?>)" 
        style="display:block;width:100%;text-align:left;padding:10px 16px;background:none;border:none;color:#e94560;font-size:.875rem;cursor:pointer;font-family:inherit;">🗑️ Delete</button>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Image or Text Post -->
  <?php if ($post['image']): ?>
  <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $post['id'] ?>">
    <img src="<?= postImageUrl($post['image']) ?>" alt="Post" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;">
  </a>
  <?php else:
    $bg = $bgMap[$post['text_bg'] ?? 'gradient-purple'] ?? $bgMap['gradient-purple'];
    $textColor = ($post['text_bg'] === 'light') ? '#111827' : '#ffffff';
  ?>
  <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $post['id'] ?>" 
  style="text-decoration:none;display:block;">
    <div style="width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:<?= $bg ?>;padding:32px;">
      <p style="color:<?= $textColor ?>;font-size:clamp(.9rem,3vw,1.4rem);font-weight:600;text-align:center;line-height:1.5;word-break:break-word;margin:0;">
        <?= nl2br(h($post['caption'])) ?></p>
    </div>
  </a>
  <?php endif; ?>

  <!-- Actions -->
  <div style="padding:10px 12px 4px;display:flex;align-items:center;gap:2px;">
    <button class="btn btn-ghost like-btn" data-id="<?= $post['id'] ?>" onclick="toggleLike(<?= $post['id'] ?>, this)" 
    style="color:<?= $post['user_liked'] ? 'var(--accent)' : 'var(--muted)' ?>;padding:8px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="<?= $post['user_liked'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
    </button>
    <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $post['id'] ?>" class="btn btn-ghost" style="color:var(--muted);padding:8px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </a>
    <?php if ($post['image']): ?>
    <a href="<?= postImageUrl($post['image']) ?>" download title="Download" class="btn btn-ghost" style="color:var(--muted);padding:8px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 3v12"/></svg>
    </a>
    <?php endif; ?>
  </div>

  <!-- Likes & Caption -->
  <div style="padding:0 14px 12px;">
    <div style="font-weight:600;font-size:.875rem;margin-bottom:4px;">
      <span class="like-count-<?= $post['id'] ?>"><?= number_format($post['like_count']) ?></span> 
      <?= $post['like_count'] == 1 ? 'like' : 'likes' ?>
    </div>
    <?php if ($post['caption'] && $post['image']): ?>
    <p style="font-size:.875rem;line-height:1.5;"><strong>
      <?= h($post['username']) ?></strong> 
      <?= nl2br(h($post['caption'])) ?></p>
    <?php endif; ?>
    <?php if ($post['comment_count'] > 0): ?>
    <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $post['id'] ?>" 
    style="color:var(--muted);font-size:.82rem;text-decoration:none;display:block;margin-top:4px;">View all <?= $post['comment_count'] ?> comments</a>
    <?php endif; ?>
    <div style="color:var(--muted);font-size:.7rem;margin-top:5px;text-transform:uppercase;letter-spacing:.05em;">
      <?= timeAgo($post['created_at']) ?></div>
  </div>

  <!-- Quick Comment -->
  <div style="border-top:1px solid var(--border);padding:8px 12px;display:flex;gap:8px;align-items:center;">
    <img src="<?= avatarUrl($me['avatar']) ?>" 
    class="avatar" width="26" height="26" alt="">
    <input type="text" class="quick-comment-input" data-id="<?= $post['id'] ?>" placeholder="Add a comment…" style="flex:1;background:transparent;border:none;outline:none;color:var(--text);font-family:inherit;font-size:.85rem;min-width:0;" inputmode="text" onkeydown="if(event.key==='Enter')submitComment(<?= $post['id'] ?>)">
    <button onclick="submitComment(<?= $post['id'] ?>)" class="btn btn-ghost" style="color:var(--accent);padding:4px 6px;font-weight:700;font-size:.82rem;flex-shrink:0;">Post</button>
  </div>

</article>
<?php endforeach; ?>
<?php endif; ?>

</main>

<script>
const CSRF = '<?= csrfToken() ?>';
const BASE = '<?= BASE_URL ?>';

async function toggleLike(postId, btn) {
  const res = await fetch(BASE + '/api/like.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `post_id=${postId}&csrf_token=${CSRF}`
  });
  const data = await res.json();
  const heart = btn.querySelector('svg');
  if (data.action === 'liked') {
    btn.style.color = 'var(--accent)';
    heart.setAttribute('fill', 'currentColor');
  } else {
    btn.style.color = 'var(--muted)';
    heart.setAttribute('fill', 'none');
  }
  document.querySelectorAll(`.like-count-${postId}`).forEach(el => el.textContent = data.count);
}

async function submitComment(postId) {
  const input = document.querySelector(`.quick-comment-input[data-id="${postId}"]`);
  const body = input.value.trim();
  if (!body) return;
  const res = await fetch(BASE + '/api/comment.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `post_id=${postId}&body=${encodeURIComponent(body)}&csrf_token=${CSRF}`
  });
  const data = await res.json();
  if (data.success) { input.value = ''; location.reload(); }
}

function toggleMenu(postId) {
  const m = document.getElementById('menu-' + postId);
  m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', e => {
  if (!e.target.closest('.post-card')) {
    document.querySelectorAll('[id^="menu-"]').forEach(m => m.style.display = 'none');
  }
});

async function deletePost(postId) {
  if (!confirm('Delete this post?')) return;
  const res = await fetch(BASE + '/api/delete-post.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `post_id=${postId}&csrf_token=${CSRF}`
  });
  const data = await res.json();
  if (data.success) document.getElementById('post-' + postId).remove();
  else alert(data.error || 'Error');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

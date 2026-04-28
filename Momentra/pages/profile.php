<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$me          = currentUser();
$username    = $_GET['u'] ?? '';
$profileUser = $username ? getUserByUsername($username) : $me;

if (!$profileUser) {
    $pageTitle = 'Not Found';
    require_once __DIR__ . '/../includes/header.php';
    echo '<main><div class="card" style="padding:40px;text-align:center;">User not found.</div></main>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle      = $profileUser['username'];
$posts          = getUserPosts($profileUser['id']);
$followerCount  = getFollowerCount($profileUser['id']);
$followingCount = getFollowingCount($profileUser['id']);
$isMe           = $profileUser['id'] === $me['id'];
$following      = !$isMe && isFollowing($me['id'], $profileUser['id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wide-main">

<!-- Profile Header -->
<div class="card" style="padding:20px 16px;margin-bottom:12px;">
  <div style="display:flex;align-items:flex-start;gap:20px;">
    <div style="flex-shrink:0;">
      <img src="<?= avatarUrl($profileUser['avatar']) ?>" class="avatar" style="width:80px;height:80px;border-width:3px;<?= !$isMe ? 'border-color:var(--accent)!important;' : '' ?>" alt="">
    </div>
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <h1 style="font-size:1.1rem;font-weight:700;word-break:break-all;"><?= h($profileUser['username']) ?></h1>
        <?php if ($isMe): ?>
          <a href="<?= BASE_URL ?>/pages/edit-profile.php" class="btn btn-outline btn-sm">Edit</a>
        <?php else: ?>
          <button id="follow-btn" onclick="toggleFollow()" class="btn <?= $following ? 'btn-outline' : 'btn-primary' ?> btn-sm">
            <?= $following ? 'Following' : 'Follow' ?>
          </button>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:12px;">
        <div style="text-align:center;">
          <div style="font-weight:700;font-size:1rem;"><?= count($posts) ?></div>
          <div style="color:var(--muted);font-size:.72rem;">Posts</div>
        </div>
        <div style="text-align:center;">
          <div style="font-weight:700;font-size:1rem;" id="follower-count"><?= $followerCount ?></div>
          <div style="color:var(--muted);font-size:.72rem;">Followers</div>
        </div>
        <div style="text-align:center;">
          <div style="font-weight:700;font-size:1rem;"><?= $followingCount ?></div>
          <div style="color:var(--muted);font-size:.72rem;">Following</div>
        </div>
      </div>
      <?php if ($profileUser['full_name']): ?>
        <div style="font-weight:600;font-size:.9rem;margin-bottom:3px;"><?= h($profileUser['full_name']) ?></div>
      <?php endif; ?>
      <?php if ($profileUser['bio']): ?>
        <p style="color:var(--muted);font-size:.85rem;line-height:1.5;"><?= nl2br(h($profileUser['bio'])) ?></p>
      <?php endif; ?>
      <?php if ($profileUser['website']): ?>
        <a href="<?= h($profileUser['website']) ?>" target="_blank" style="color:var(--accent);font-size:.85rem;text-decoration:none;word-break:break-all;"><?= h($profileUser['website']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Posts Grid -->
<?php if (empty($posts)): ?>
<div class="card" style="padding:40px 24px;text-align:center;">
  <div style="font-size:2.5rem;margin-bottom:12px;">📷</div>
  <p style="color:var(--muted);"><?= $isMe ? "You haven't posted anything yet." : 'No posts yet.' ?></p>
  <?php if ($isMe): ?>
    <a href="<?= BASE_URL ?>/pages/create.php" class="btn btn-primary" style="margin-top:16px;">Share your first photo</a>
  <?php endif; ?>
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
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2px;border-radius:var(--radius);overflow:hidden;">
  <?php foreach ($posts as $p): ?>
  <a href="<?= BASE_URL ?>/pages/post.php?id=<?= $p['id'] ?>" style="position:relative;aspect-ratio:1;overflow:hidden;display:block;background:var(--card);">
    <?php if ($p['image']): ?>
      <img src="<?= postImageUrl($p['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
    <?php else:
      $bg = $bgMap[$p['text_bg'] ?? 'gradient-purple'] ?? $bgMap['gradient-purple'];
      $tc = ($p['text_bg'] === 'light') ? '#111827' : '#fff';
    ?>
      <div style="width:100%;height:100%;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;padding:8px;">
        <p style="color:<?= $tc ?>;font-size:clamp(.55rem,2.5vw,.8rem);font-weight:600;text-align:center;line-height:1.3;word-break:break-word;overflow:hidden;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;"><?= h($p['caption']) ?></p>
      </div>
    <?php endif; ?>
    <div style="position:absolute;inset:0;background:#00000055;opacity:0;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:14px;color:#fff;font-weight:700;font-size:.85rem;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
      <span>❤️ <?= $p['like_count'] ?></span>
      <span>💬 <?= $p['comment_count'] ?></span>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

</div>

<script>
const CSRF   = '<?= csrfToken() ?>';
const BASE   = '<?= BASE_URL ?>';
const userId = <?= $profileUser['id'] ?>;

async function toggleFollow() {
  const res  = await fetch(BASE + '/api/follow.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`following_id=${userId}&csrf_token=${CSRF}` });
  const data = await res.json();
  const btn  = document.getElementById('follow-btn');
  if (data.action === 'followed') {
    btn.className = 'btn btn-outline btn-sm';
    btn.textContent = 'Following';
  } else {
    btn.className = 'btn btn-primary btn-sm';
    btn.textContent = 'Follow';
  }
  document.getElementById('follower-count').textContent = data.follower_count;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

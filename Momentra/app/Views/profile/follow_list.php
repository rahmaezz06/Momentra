<?php $heading = $type === 'following' ? 'Following' : 'Followers'; ?>
<div style="max-width:480px;margin:0 auto;padding:calc(var(--nav-h) + 20px) 12px 20px;">

  <div class="card" style="padding:14px 16px;margin-bottom:12px;display:flex;align-items:center;gap:12px;">
    <a href="<?= BASE_URL ?>/profile?u=<?= h($user['username']) ?>" style="color:var(--text);line-height:0;">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    </a>
    <div>
      <div style="font-size:.75rem;color:var(--muted);"><?= h($user['username']) ?></div>
      <div style="font-weight:700;font-size:1rem;"><?= $heading ?></div>
    </div>
    <div style="margin-left:auto;color:var(--muted);font-size:.85rem;"><?= count($list) ?></div>
  </div>

  <?php if (empty($list)): ?>
  <div class="card" style="padding:40px 24px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:12px;"><?= $type === 'followers' ? '👥' : '🔍' ?></div>
    <p style="color:var(--muted);">
      <?php if ($type === 'followers'): ?>
        <?= $user['id'] === $me['id'] ? "You don't have any followers yet." : h($user['username']) . " doesn't have any followers yet." ?>
      <?php else: ?>
        <?= $user['id'] === $me['id'] ? "You're not following anyone yet." : h($user['username']) . " isn't following anyone yet." ?>
      <?php endif; ?>
    </p>
  </div>
  <?php else: ?>
  <div class="card" style="overflow:hidden;">
    <?php foreach ($list as $i => $u): ?>
    <?php $viewerFollows = (bool)$u['viewer_follows']; $isMe = $u['id'] === $me['id']; ?>
    <div id="row-<?= $u['id'] ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;<?= $i > 0 ? 'border-top:1px solid var(--border);' : '' ?>">
      <a href="<?= BASE_URL ?>/profile?u=<?= h($u['username']) ?>" style="flex-shrink:0;">
        <img src="<?= avatarUrl($u['avatar']) ?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" alt="">
      </a>
      <div style="flex:1;min-width:0;">
        <a href="<?= BASE_URL ?>/profile?u=<?= h($u['username']) ?>" style="font-weight:700;font-size:.9rem;color:var(--text);text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= h($u['username']) ?></a>
        <?php if ($u['full_name']): ?>
          <div style="font-size:.8rem;color:var(--muted);"><?= h($u['full_name']) ?></div>
        <?php endif; ?>
      </div>
      <?php if (!$isMe): ?>
      <button onclick="toggleFollow(this, <?= $u['id'] ?>)" data-following="<?= $viewerFollows ? '1' : '0' ?>"
        class="btn <?= $viewerFollows ? 'btn-outline' : 'btn-primary' ?> btn-sm" style="flex-shrink:0;min-width:80px;">
        <?= $viewerFollows ? 'Following' : 'Follow' ?>
      </button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
const CSRF = '<?= csrfToken() ?>';
const BASE = '<?= BASE_URL ?>';

async function toggleFollow(btn, userId) {
  btn.disabled = true;
  try {
    const res  = await fetch(BASE + '/api/follow', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`following_id=${userId}&csrf_token=${CSRF}` });
    const data = await res.json();
    if (data.action === 'followed') { btn.className='btn btn-outline btn-sm'; btn.textContent='Following'; }
    else { btn.className='btn btn-primary btn-sm'; btn.textContent='Follow'; }
  } catch(e) { console.error(e); }
  btn.disabled = false;
}
</script>

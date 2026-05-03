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
$isLiked    = (bool) db_fetch_one('SELECT 1 FROM likes WHERE post_id=? AND user_id=?', 'ii', [$post['id'], $me['id']]);
$isSaved    = (bool) db_fetch_one('SELECT 1 FROM saved_posts WHERE post_id=? AND user_id=?', 'ii', [$post['id'], $me['id']]);
$likeCount  = (int) $post['like_count'];
$isOwner    = ($post['user_id'] === $me['id']);
?>
<main>
<div class="card" style="display:flex;flex-direction:column;">

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);">
    <a href="<?= BASE_URL ?>/profile?u=<?= h($post['username']) ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
      <img src="<?= avatarUrl($post['avatar']) ?>" class="avatar" width="42" height="42" alt="">
      <div>
        <div style="font-weight:700;"><?= h($post['username']) ?></div>
        <?php if ($post['location']): ?><div style="font-size:.75rem;color:var(--muted);">📍 <?= h($post['location']) ?></div><?php endif; ?>
      </div>
    </a>
    <?php if ($isOwner): ?>
    <div style="display:flex;gap:8px;">
      <a href="<?= BASE_URL ?>/post/edit?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
      <button onclick="deletePost(<?= $post['id'] ?>)" class="btn btn-danger btn-sm">Delete</button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Media -->
  <?php if ($post['image']): ?>
  <img src="<?= postImageUrl($post['image']) ?>" alt="Post" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;">
  <?php else:
    $bg = $bgMap[$post['text_bg'] ?? 'gradient-purple'] ?? $bgMap['gradient-purple'];
    $tc = ($post['text_bg'] === 'light') ? '#111827' : '#ffffff';
  ?>
  <div style="width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:<?= $bg ?>;padding:40px;">
    <p style="color:<?= $tc ?>;font-size:clamp(1rem,4vw,1.8rem);font-weight:600;text-align:center;line-height:1.5;"><?= nl2br(h($post['caption'])) ?></p>
  </div>
  <?php endif; ?>

  <!-- Actions -->
  <div style="padding:12px 16px 4px;display:flex;align-items:center;gap:4px;border-bottom:1px solid var(--border);">
    <button id="like-btn" onclick="toggleLike()" style="background:none;border:none;cursor:pointer;padding:8px;color:<?= $isLiked ? 'var(--accent)' : 'var(--muted)' ?>;display:flex;align-items:center;gap:6px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
    </button>
    <button onclick="document.getElementById('comment-input').focus()" style="background:none;border:none;cursor:pointer;padding:8px;color:var(--muted);">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </button>
    <?php if ($post['image']): ?>
    <a href="<?= postImageUrl($post['image']) ?>" download style="background:none;border:none;cursor:pointer;padding:8px;color:var(--muted);display:flex;align-items:center;text-decoration:none;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 3v12"/></svg>
    </a>
    <?php endif; ?>
    <button id="save-btn" onclick="toggleSave()" style="background:none;border:none;cursor:pointer;padding:8px;color:<?= $isSaved ? 'var(--accent)' : 'var(--muted)' ?>;display:flex;align-items:center;margin-left:auto;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5z"/></svg>
    </button>
  </div>

  <!-- Likes -->
  <div style="padding:10px 16px 0;font-weight:700;font-size:.875rem;">
    <span id="like-count"><?= $likeCount ?></span> <?= $likeCount == 1 ? 'like' : 'likes' ?>
  </div>

  <!-- Caption -->
  <?php if ($post['caption'] && $post['image']): ?>
  <div style="padding:6px 16px;">
    <strong><?= h($post['username']) ?></strong> <?= nl2br(h($post['caption'])) ?>
  </div>
  <?php endif; ?>

  <div style="color:var(--muted);font-size:.7rem;padding:4px 16px 12px;text-transform:uppercase;"><?= timeAgo($post['created_at']) ?></div>

  <!-- Comments -->
  <div style="border-top:1px solid var(--border);padding:12px 16px;" id="comments-section">
    <?php foreach ($comments as $comment): ?>
    <div class="comment-block" id="comment-<?= $comment['id'] ?>" style="display:flex;gap:10px;margin-bottom:14px;">
      <a href="<?= BASE_URL ?>/profile?u=<?= h($comment['username']) ?>">
        <img src="<?= avatarUrl($comment['avatar']) ?>" class="avatar" width="32" height="32">
      </a>
      <div style="flex:1;">
        <span style="font-weight:700;font-size:.875rem;"><?= h($comment['username']) ?></span>
        <span style="font-size:.875rem;"> <?= nl2br(h($comment['body'])) ?></span>
        <div style="display:flex;gap:12px;margin-top:4px;align-items:center;">
          <span style="font-size:.72rem;color:var(--muted);"><?= timeAgo($comment['created_at']) ?></span>
          <button onclick="setReply(<?= $comment['id'] ?>, '<?= h($comment['username']) ?>')" style="background:none;border:none;font-size:.72rem;color:var(--muted);cursor:pointer;font-weight:600;">Reply</button>
          <!-- Like على الكومنت -->
          <button id="clike-<?= $comment['id'] ?>" onclick="toggleCommentLike(<?= $comment['id'] ?>)" style="background:none;border:none;font-size:.72rem;cursor:pointer;display:flex;align-items:center;gap:3px;color:<?= $comment['user_liked'] ? 'var(--accent)' : 'var(--muted)' ?>;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="<?= $comment['user_liked'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
            <span id="clike-count-<?= $comment['id'] ?>"><?= $comment['likes_count'] > 0 ? $comment['likes_count'] : '' ?></span>
          </button>
          <?php if ($comment['user_id'] === $me['id'] || $isOwner): ?>
          <button onclick="deleteComment(<?= $comment['id'] ?>, <?= $post['user_id'] ?>)" style="background:none;border:none;font-size:.72rem;color:#e94560;cursor:pointer;">Delete</button>
          <?php endif; ?>
        </div>
        <!-- Replies -->
        <?php if (!empty($comment['replies'])): ?>
        <div style="margin-top:10px;padding-left:8px;border-left:2px solid var(--border);">
          <?php foreach ($comment['replies'] as $reply): ?>
          <div id="comment-<?= $reply['id'] ?>" style="display:flex;gap:8px;margin-bottom:10px;">
            <img src="<?= avatarUrl($reply['avatar']) ?>" class="avatar" width="26" height="26">
            <div>
              <span style="font-weight:700;font-size:.82rem;"><?= h($reply['username']) ?></span>
              <span style="font-size:.82rem;"> <?= nl2br(h($reply['body'])) ?></span>
              <div style="display:flex;gap:10px;margin-top:3px;align-items:center;">
                <span style="font-size:.7rem;color:var(--muted);"><?= timeAgo($reply['created_at']) ?></span>
                <!-- Like على الرد -->
                <button id="clike-<?= $reply['id'] ?>" onclick="toggleCommentLike(<?= $reply['id'] ?>)" style="background:none;border:none;font-size:.7rem;cursor:pointer;display:flex;align-items:center;gap:3px;color:<?= $reply['user_liked'] ? 'var(--accent)' : 'var(--muted)' ?>;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="<?= $reply['user_liked'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
                  <span id="clike-count-<?= $reply['id'] ?>"><?= $reply['likes_count'] > 0 ? $reply['likes_count'] : '' ?></span>
                </button>
                <?php if ($reply['user_id'] === $me['id'] || $isOwner): ?>
                <button onclick="deleteComment(<?= $reply['id'] ?>, <?= $post['user_id'] ?>)" style="background:none;border:none;font-size:.7rem;color:#e94560;cursor:pointer;">Delete</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Add comment -->
  <div style="border-top:1px solid var(--border);padding:10px 14px;display:flex;gap:10px;align-items:flex-end;position:sticky;bottom:0;background:var(--card);">
    <img src="<?= avatarUrl($me['avatar']) ?>" class="avatar" width="30" height="30">
    <div style="flex:1;">
      <div id="reply-indicator" style="display:none;font-size:.75rem;color:var(--accent);margin-bottom:4px;"></div>
      <input type="text" id="comment-input" placeholder="Add a comment…" style="width:100%;background:transparent;border:none;outline:none;color:var(--text);font-family:inherit;font-size:.875rem;" onkeydown="if(event.key==='Enter')submitComment()">
    </div>
    <button onclick="submitComment()" class="btn btn-ghost" style="color:var(--accent);font-weight:700;font-size:.82rem;flex-shrink:0;">Post</button>
  </div>
</div>
</main>

<script>
const CSRF   = '<?= csrfToken() ?>';
const BASE   = '<?= BASE_URL ?>';
const postId = <?= $post['id'] ?>;
let replyTo  = null;

async function toggleLike() {
  const res  = await fetch(BASE + '/api/like', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`post_id=${postId}&csrf_token=${CSRF}` });
  const data = await res.json();
  const btn  = document.getElementById('like-btn');
  const svg  = btn.querySelector('svg');
  btn.style.color = data.action === 'liked' ? 'var(--accent)' : 'var(--muted)';
  svg.setAttribute('fill', data.action === 'liked' ? 'currentColor' : 'none');
  document.getElementById('like-count').textContent = data.count;
}

async function toggleSave() {
  const res  = await fetch(BASE + '/api/save', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`post_id=${postId}&csrf_token=${CSRF}` });
  const data = await res.json();
  const btn  = document.getElementById('save-btn');
  const svg  = btn.querySelector('svg');
  btn.style.color = data.action === 'saved' ? 'var(--accent)' : 'var(--muted)';
  svg.setAttribute('fill', data.action === 'saved' ? 'currentColor' : 'none');
}

function setReply(commentId, username) {
  replyTo = commentId;
  const indicator = document.getElementById('reply-indicator');
  indicator.style.display = 'block';
  indicator.innerHTML = `Replying to @${username} <button onclick="clearReply()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.75rem;">✕</button>`;
  document.getElementById('comment-input').focus();
}

function clearReply() {
  replyTo = null;
  document.getElementById('reply-indicator').style.display = 'none';
}

async function submitComment() {
  const input = document.getElementById('comment-input');
  const body  = input.value.trim();
  if (!body) return;
  const params = `post_id=${postId}&body=${encodeURIComponent(body)}&csrf_token=${CSRF}${replyTo ? '&parent_id=' + replyTo : ''}`;
  const res    = await fetch(BASE + '/api/comment', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: params });
  const data   = await res.json();
  if (data.success) { input.value = ''; clearReply(); location.reload(); }
}

async function toggleCommentLike(commentId) {
  const res  = await fetch(BASE + '/api/like-comment', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`comment_id=${commentId}&csrf_token=${CSRF}` });
  const data = await res.json();
  const btn  = document.getElementById('clike-' + commentId);
  const svg  = btn.querySelector('svg');
  const countEl = document.getElementById('clike-count-' + commentId);
  btn.style.color = data.action === 'liked' ? 'var(--accent)' : 'var(--muted)';
  svg.setAttribute('fill', data.action === 'liked' ? 'currentColor' : 'none');
  countEl.textContent = data.count > 0 ? data.count : '';
}

async function deleteComment(commentId, postOwnerId = 0) {
  if (!confirm('Delete comment?')) return;
  const res  = await fetch(BASE + '/api/delete-comment', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`comment_id=${commentId}&post_owner_id=${postOwnerId}&csrf_token=${CSRF}` });
  const data = await res.json();
  if (data.success) document.getElementById('comment-' + commentId)?.remove();
}

async function deletePost(postId) {
  if (!confirm('Delete this post?')) return;
  const res  = await fetch(BASE + '/api/delete-post', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`post_id=${postId}&csrf_token=${CSRF}` });
  const data = await res.json();
  if (data.success) location.href = BASE + '/';
  else alert(data.error || 'Error');
}
</script>

<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$me     = currentUser();
$postId = (int)($_GET['id'] ?? 0);
$post   = getPost($postId);

if (!$post) {
    $pageTitle = 'Not Found';
    require_once __DIR__ . '/../includes/header.php';
    echo '<main><div class="card" style="padding:40px;text-align:center;">Post not found.</div></main>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Get comments with replies using the new function
$comments = getPostCommentsWithReplies($postId, $me['id']);

// Is liked by current user?
$stmt = db()->prepare('SELECT 1 FROM likes WHERE post_id=? AND user_id=?');
$stmt->execute([$postId, $me['id']]);
$isLiked = (bool)$stmt->fetchColumn();

$likeCount   = (int)$post['like_count'];
$pageTitle   = $post['username'] . "'s photo";
$isPostOwner = ($post['user_id'] === $me['id']);

// Is saved by current user?
$stmt = db()->prepare('SELECT 1 FROM saved_posts WHERE post_id=? AND user_id=?');
$stmt->execute([$postId, $me['id']]);
$isSaved = (bool)$stmt->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<main>
<div class="card" style="display:flex;flex-direction:column;">

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);">
    <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($post['username']) ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
      <img src="<?= avatarUrl($post['avatar']) ?>" class="avatar" width="42" height="42" alt="">
      <div>
        <div style="font-weight:700;"><?= h($post['username']) ?></div>
        <?php if ($post['location']): ?><div style="font-size:.75rem;color:var(--muted);">📍 <?= h($post['location']) ?></div><?php endif; ?>
      </div>
    </a>
    <?php if ($post['user_id'] === $me['id']): ?>
    <div style="display:flex;gap:8px;">
      <a href="<?= BASE_URL ?>/pages/edit-post.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
      <button onclick="deletePost(<?= $post['id'] ?>)" class="btn btn-danger btn-sm">Delete</button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Image or Text Post -->
  <?php if ($post['image']): ?>
  <img src="<?= postImageUrl($post['image']) ?>" alt="Post image" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;">
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
    $bg = $bgMap[$post['text_bg'] ?? 'gradient-purple'] ?? $bgMap['gradient-purple'];
    $textColor = ($post['text_bg'] === 'light') ? '#111827' : '#ffffff';
  ?>
  <div style="width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:<?= $bg ?>;padding:40px;">
    <p style="color:<?= $textColor ?>;font-size:clamp(1rem,4vw,1.8rem);font-weight:600;text-align:center;line-height:1.5;word-break:break-word;"><?= nl2br(h($post['caption'])) ?></p>
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
    <a href="<?= postImageUrl($post['image']) ?>" download title="Download" style="background:none;border:none;cursor:pointer;padding:8px;color:var(--muted);display:flex;align-items:center;text-decoration:none;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 3v12"/></svg>
    </a>
    <?php endif; ?>
    <button id="save-btn" onclick="toggleSave()" style="background:none;border:none;cursor:pointer;padding:8px;color:<?= $isSaved ? 'var(--accent)' : 'var(--muted)' ?>;display:flex;align-items:center;margin-left:auto;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5z"/></svg>
    </button>
  </div>

  <!-- Likes count — clickable to see who liked -->
  <?php if ($likeCount > 0): ?>
  <div style="padding:10px 16px 0;">
    <button onclick="showLikes()" style="background:none;border:none;cursor:pointer;padding:0;font-weight:700;font-size:.875rem;color:var(--text);">
      <span id="like-count"><?= $likeCount ?></span> <?= $likeCount == 1 ? 'like' : 'likes' ?>
    </button>
  </div>
  <?php else: ?>
  <div style="padding:10px 16px 0;font-weight:700;font-size:.875rem;">
    <span id="like-count">0</span> likes
  </div>
  <?php endif; ?>

  <!-- Caption -->
  <?php if ($post['caption'] && $post['image']): ?>
  <div style="padding:8px 16px 4px;">
    <strong><?= h($post['username']) ?></strong> <?= nl2br(h($post['caption'])) ?>
    <div style="color:var(--muted);font-size:.7rem;margin-top:4px;text-transform:uppercase;letter-spacing:.05em;"><?= timeAgo($post['created_at']) ?></div>
  </div>
  <?php elseif ($post['image']): ?>
  <div style="padding:4px 16px;">
    <div style="color:var(--muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;"><?= timeAgo($post['created_at']) ?></div>
  </div>
  <?php else: ?>
  <div style="padding:4px 16px;">
    <div style="color:var(--muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;"><?= timeAgo($post['created_at']) ?></div>
  </div>
  <?php endif; ?>

  <!-- Comments -->
  <div id="comments-list" style="max-height:400px;overflow-y:auto;padding:8px 14px;">
    <?php foreach ($comments as $c): ?>
    <div class="comment-item" id="comment-<?= $c['id'] ?>" style="margin-bottom:14px;">
      <div style="display:flex;align-items:flex-start;gap:8px;">
        <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($c['username']) ?>">
          <img src="<?= avatarUrl($c['avatar']) ?>" class="avatar" width="30" height="30" alt="">
        </a>
        <div style="flex:1;min-width:0;">
          <div style="background:var(--surface);padding:8px 12px;border-radius:var(--radius);">
            <span style="font-weight:600;font-size:.875rem;"><?= h($c['username']) ?></span>
            <span style="font-size:.875rem;margin-left:6px;"><?= nl2br(h($c['body'])) ?></span>
          </div>
          <div style="display:flex;gap:10px;margin-top:4px;margin-left:6px;flex-wrap:wrap;">
            <button class="like-comment-btn" data-id="<?= $c['id'] ?>" data-liked="<?= $c['user_liked'] ? '1' : '0' ?>" style="background:none;border:none;color:<?= $c['user_liked'] ? 'var(--accent)' : 'var(--muted)' ?>;cursor:pointer;font-size:.72rem;display:flex;align-items:center;gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="<?= $c['user_liked'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
              <span class="like-count"><?= $c['likes_count'] ?? 0 ?></span>
            </button>
            <button class="reply-btn" data-id="<?= $c['id'] ?>" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.72rem;display:flex;align-items:center;gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              Reply <span class="reply-count"><?= $c['replies_count'] ?? 0 ?></span>
            </button>
            <?php if ($c['user_id'] === $me['id'] || $isPostOwner): ?>
            <button onclick="deleteComment(<?= $c['id'] ?>)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.72rem;">Delete</button>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Replies -->
      <div class="replies-container" data-parent="<?= $c['id'] ?>" style="margin-left:38px;margin-top:8px;">
        <?php if (!empty($c['replies'])): ?>
          <?php foreach ($c['replies'] as $reply): ?>
          <div class="reply-item" id="comment-<?= $reply['id'] ?>" style="display:flex;align-items:flex-start;gap:8px;margin-bottom:10px;">
            <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($reply['username']) ?>">
              <img src="<?= avatarUrl($reply['avatar']) ?>" class="avatar" width="26" height="26" alt="">
            </a>
            <div style="flex:1;min-width:0;">
              <div style="background:var(--surface);padding:6px 10px;border-radius:var(--radius);">
                <span style="font-weight:600;font-size:.8rem;"><?= h($reply['username']) ?></span>
                <span style="font-size:.8rem;margin-left:6px;"><?= nl2br(h($reply['body'])) ?></span>
              </div>
              <div style="display:flex;gap:10px;margin-top:4px;margin-left:6px;">
                <button class="like-comment-btn" data-id="<?= $reply['id'] ?>" data-liked="<?= $reply['user_liked'] ? '1' : '0' ?>" style="background:none;border:none;color:<?= $reply['user_liked'] ? 'var(--accent)' : 'var(--muted)' ?>;cursor:pointer;font-size:.7rem;display:flex;align-items:center;gap:4px;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="<?= $reply['user_liked'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
                  <span class="like-count"><?= $reply['likes_count'] ?? 0 ?></span>
                </button>
                <?php if ($reply['user_id'] === $me['id'] || $isPostOwner): ?>
                <button onclick="deleteComment(<?= $reply['id'] ?>)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.7rem;">Delete</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($comments)): ?>
    <p style="color:var(--muted);text-align:center;padding:20px 0;font-size:.875rem;">No comments yet. Be the first!</p>
    <?php endif; ?>
  </div>

  <!-- Add Comment -->
  <div style="display:flex;gap:8px;align-items:center;padding:10px 14px;border-top:1px solid var(--border);">
    <img src="<?= avatarUrl($me['avatar']) ?>" class="avatar" width="30" height="30" alt="">
    <input id="comment-input" type="text" placeholder="Add a comment…" style="flex:1;background:transparent;border:none;outline:none;color:var(--text);font-family:inherit;font-size:.875rem;min-width:0;" onkeydown="if(event.key==='Enter')submitComment()">
    <button onclick="submitComment()" class="btn btn-ghost" style="color:var(--accent);font-weight:700;flex-shrink:0;">Post</button>
  </div>

</div>
</main>

<!-- Likes Modal -->
<div id="likes-modal" style="display:none;position:fixed;inset:0;background:#00000070;z-index:999;align-items:flex-end;justify-content:center;" onclick="if(event.target===this)closeLikes()">
  <div style="background:var(--card);border-radius:20px 20px 0 0;width:100%;max-width:480px;max-height:70vh;display:flex;flex-direction:column;padding-bottom:max(16px,env(safe-area-inset-bottom));">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
      <h3 style="font-weight:700;font-size:1rem;">Liked by</h3>
      <button onclick="closeLikes()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:1.4rem;line-height:1;">×</button>
    </div>
    <div id="likes-list" style="overflow-y:auto;padding:8px 0;">
      <p style="text-align:center;color:var(--muted);padding:24px;font-size:.875rem;">Loading…</p>
    </div>
  </div>
</div>

<script>
const CSRF      = '<?= csrfToken() ?>';
const BASE      = '<?= BASE_URL ?>';
const postId    = <?= $post['id'] ?>;

// Show who liked
async function showLikes() {
  const modal = document.getElementById('likes-modal');
  const list  = document.getElementById('likes-list');
  modal.style.display = 'flex';
  list.innerHTML = '<p style="text-align:center;color:var(--muted);padding:24px;font-size:.875rem;">Loading…</p>';
  try {
    const res  = await fetch(BASE + '/api/get_likes.php?post_id=' + postId);
    const data = await res.json();
    if (!data.likers || !data.likers.length) {
      list.innerHTML = '<p style="text-align:center;color:var(--muted);padding:24px;font-size:.875rem;">No likes yet.</p>';
      return;
    }
    list.innerHTML = data.likers.map(u => `
      <a href="${BASE}/pages/profile.php?u=${encodeURIComponent(u.username)}" style="display:flex;align-items:center;gap:12px;padding:10px 20px;text-decoration:none;color:var(--text);transition:background .15s;" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
        <img src="${u.avatar_url}" width="40" height="40" style="border-radius:50%;object-fit:cover;border:2px solid var(--border);">
        <div>
          <div style="font-weight:600;font-size:.9rem;">${u.username}</div>
          ${u.full_name ? `<div style="font-size:.78rem;color:var(--muted);">${u.full_name}</div>` : ''}
        </div>
      </a>`).join('');
  } catch(e) {
    list.innerHTML = '<p style="text-align:center;color:var(--muted);padding:24px;">Could not load likes.</p>';
  }
}

function closeLikes() {
  document.getElementById('likes-modal').style.display = 'none';
}

// Toggle like on post
async function toggleSave() {
  const res  = await fetch(BASE + '/api/save.php', { 
    method:'POST', 
    headers:{'Content-Type':'application/x-www-form-urlencoded'}, 
    body:`post_id=${postId}&csrf_token=${CSRF}` 
  });
  const data = await res.json();
  const btn  = document.getElementById('save-btn');
  const svg  = btn.querySelector('svg');
  if (data.action === 'saved') { 
    btn.style.color='var(--accent)'; 
    svg.setAttribute('fill','currentColor'); 
  }
  else { 
    btn.style.color='var(--muted)'; 
    svg.setAttribute('fill','none'); 
  }
}

async function toggleLike() {
  const res  = await fetch(BASE + '/api/like.php', { 
    method:'POST', 
    headers:{'Content-Type':'application/x-www-form-urlencoded'}, 
    body:`post_id=${postId}&csrf_token=${CSRF}` 
  });
  const data = await res.json();
  const btn  = document.getElementById('like-btn');
  const svg  = btn.querySelector('svg');
  if (data.action === 'liked') { 
    btn.style.color='var(--accent)'; 
    svg.setAttribute('fill','currentColor'); 
  }
  else { 
    btn.style.color='var(--muted)'; 
    svg.setAttribute('fill','none'); 
  }
  document.getElementById('like-count').textContent = data.count;
}

// Submit new comment
async function submitComment(parentId = 0) {
  const input = parentId ? document.querySelector(`.reply-input[data-parent="${parentId}"]`) : document.getElementById('comment-input');
  const body  = input?.value.trim();
  if (!body) return;
  
  let url = BASE + '/api/comment.php';
  let bodyData = `post_id=${postId}&body=${encodeURIComponent(body)}&csrf_token=${CSRF}`;
  if (parentId) {
    bodyData += `&parent_id=${parentId}`;
  }
  
  const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: bodyData });
  const data = await res.json();
  
  if (data.success) {
    if (parentId) {
      const replyWrapper = input.closest('.reply-input-wrapper');
      replyWrapper?.remove();
      
      const repliesContainer = document.querySelector(`.replies-container[data-parent="${parentId}"]`);
      if (repliesContainer) {
        const replyHtml = createReplyHtml(data.comment);
        repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
        
        const replyBtn = document.querySelector(`.reply-btn[data-id="${parentId}"] .reply-count`);
        if (replyBtn) {
          let count = parseInt(replyBtn.textContent) || 0;
          replyBtn.textContent = count + 1;
        }
      }
    } else {
      const input = document.getElementById('comment-input');
      input.value = '';
      const list = document.getElementById('comments-list');
      const p = list.querySelector('p');
      if (p) p.remove();
      
      const commentHtml = createCommentHtml(data.comment);
      list.insertAdjacentHTML('afterbegin', commentHtml);
      list.scrollTop = 0;
    }
  }
}

function createCommentHtml(comment) {
  return `
    <div class="comment-item" id="comment-${comment.id}" style="margin-bottom:16px;">
      <div style="display:flex;align-items:flex-start;gap:10px;">
        <a href="${BASE}/pages/profile.php?u=${encodeURIComponent(comment.username)}">
          <img src="${comment.avatar_url}" class="avatar" width="32" height="32" alt="">
        </a>
        <div style="flex:1;">
          <div style="background:var(--surface);padding:8px 12px;border-radius:var(--radius);">
            <span style="font-weight:600;font-size:.875rem;">${escapeHtml(comment.username)}</span>
            <span style="font-size:.875rem;margin-left:6px;">${escapeHtml(comment.body)}</span>
          </div>
          <div style="display:flex;gap:12px;margin-top:4px;margin-left:8px;">
            <button class="like-comment-btn" data-id="${comment.id}" data-liked="0" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.72rem;display:flex;align-items:center;gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
              <span class="like-count">0</span>
            </button>
            <button class="reply-btn" data-id="${comment.id}" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.72rem;display:flex;align-items:center;gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              Reply <span class="reply-count">0</span>
            </button>
            <button onclick="deleteComment(${comment.id})" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.72rem;">Delete</button>
          </div>
        </div>
      </div>
      <div class="replies-container" data-parent="${comment.id}" style="margin-left:42px;margin-top:8px;"></div>
    </div>
  `;
}

function createReplyHtml(reply) {
  return `
    <div class="reply-item" id="comment-${reply.id}" style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;">
      <a href="${BASE}/pages/profile.php?u=${encodeURIComponent(reply.username)}">
        <img src="${reply.avatar_url}" class="avatar" width="28" height="28" alt="">
      </a>
      <div style="flex:1;">
        <div style="background:var(--surface);padding:6px 10px;border-radius:var(--radius);">
          <span style="font-weight:600;font-size:.8rem;">${escapeHtml(reply.username)}</span>
          <span style="font-size:.8rem;margin-left:6px;">${escapeHtml(reply.body)}</span>
        </div>
        <div style="display:flex;gap:12px;margin-top:4px;margin-left:8px;">
          <button class="like-comment-btn" data-id="${reply.id}" data-liked="0" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.7rem;display:flex;align-items:center;gap:4px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364l-6.364 6.364a1 1 0 0 1-1.414 0L4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/></svg>
            <span class="like-count">0</span>
          </button>
          <button onclick="deleteComment(${reply.id})" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.7rem;">Delete</button>
        </div>
      </div>
    </div>
  `;
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

async function likeComment(commentId, btn) {
  const formData = new FormData();
  formData.append('comment_id', commentId);
  formData.append('csrf_token', CSRF);
  
  const res = await fetch(BASE + '/api/like_comment.php', {
    method: 'POST',
    body: formData
  });
  const data = await res.json();
  
  if (data.success) {
    const likeSpan = btn.querySelector('.like-count');
    if (likeSpan) likeSpan.textContent = data.likes_count;
    
    const svg = btn.querySelector('svg');
    if (data.user_liked) {
      btn.style.color = 'var(--accent)';
      svg.setAttribute('fill', 'currentColor');
    } else {
      btn.style.color = 'var(--muted)';
      svg.setAttribute('fill', 'none');
    }
  }
}

async function deleteComment(cid) {
  if (!confirm('Delete this comment?')) return;
  const res  = await fetch(BASE + '/api/delete-comment.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`comment_id=${cid}&csrf_token=${CSRF}` });
  const data = await res.json();
  if (data.success) {
    const commentElement = document.getElementById('comment-' + cid);
    if (commentElement) commentElement.remove();
  }
}

async function deletePost(pid) {
  if (!confirm('Delete this post?')) return;
  const res  = await fetch(BASE + '/api/delete-post.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`post_id=${pid}&csrf_token=${CSRF}` });
  const data = await res.json();
  if (data.success) {
    // Fade out then redirect to create page
    document.querySelector('main').style.transition = 'opacity .3s';
    document.querySelector('main').style.opacity = '0';
    setTimeout(() => { window.location.href = BASE + '/pages/create.php'; }, 300);
  } else {
    alert(data.error || 'Could not delete post.');
  }
}

// Handle reply button clicks
document.addEventListener('click', async (e) => {
  if (e.target.closest('.reply-btn')) {
    const btn = e.target.closest('.reply-btn');
    const commentId = btn.dataset.id;
    const repliesContainer = document.querySelector(`.replies-container[data-parent="${commentId}"]`);
    
    if (repliesContainer.querySelector('.reply-input-wrapper')) {
      return;
    }
    
    const replyHtml = `
      <div class="reply-input-wrapper" style="margin-top:8px;">
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <img src="<?= avatarUrl($me['avatar']) ?>" class="avatar" width="28" height="28" alt="">
          <div style="flex:1;">
            <input type="text" class="reply-input" data-parent="${commentId}" placeholder="Write a reply..." style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:8px 12px;font-size:.8rem;outline:none;">
            <div style="display:flex;gap:8px;margin-top:6px;">
              <button class="submit-reply-btn btn btn-primary btn-sm">Post</button>
              <button class="cancel-reply-btn btn btn-outline btn-sm">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    `;
    repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
    
    const input = repliesContainer.querySelector('.reply-input');
    input.focus();
  }
  
  if (e.target.closest('.submit-reply-btn')) {
    const btn = e.target.closest('.submit-reply-btn');
    const wrapper = btn.closest('.reply-input-wrapper');
    const input = wrapper.querySelector('.reply-input');
    const parentId = input.dataset.parent;
    if (input.value.trim()) {
      await submitComment(parentId);
    }
  }
  
  if (e.target.closest('.cancel-reply-btn')) {
    const btn = e.target.closest('.cancel-reply-btn');
    const wrapper = btn.closest('.reply-input-wrapper');
    wrapper.remove();
  }
  
  if (e.target.closest('.like-comment-btn')) {
    const btn = e.target.closest('.like-comment-btn');
    const commentId = btn.dataset.id;
    await likeComment(commentId, btn);
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
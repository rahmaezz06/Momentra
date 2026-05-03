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
<main>
<div class="card" style="padding:24px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:20px;">🔖 Saved Posts</h2>

  <?php if (empty($posts)): ?>
  <div style="text-align:center;padding:48px 24px;">
    <div style="font-size:3rem;margin-bottom:16px;">🔖</div>
    <p style="color:var(--muted);">No saved posts yet.</p>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:4px;">
    <?php foreach ($posts as $post): ?>
    <a href="<?= BASE_URL ?>/post?id=<?= $post['id'] ?>" style="display:block;aspect-ratio:1;overflow:hidden;border-radius:6px;position:relative;text-decoration:none;">
      <?php if ($post['image']): ?>
        <img src="<?= postImageUrl($post['image']) ?>" style="width:100%;height:100%;object-fit:cover;">
      <?php else:
        $bg = $bgMap[$post['text_bg'] ?? 'gradient-purple'] ?? $bgMap['gradient-purple'];
        $tc = ($post['text_bg'] === 'light') ? '#111827' : '#ffffff';
      ?>
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $bg ?>;padding:12px;">
          <p style="color:<?= $tc ?>;font-size:.75rem;font-weight:600;text-align:center;line-height:1.4;word-break:break-word;margin:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;"><?= h($post['caption']) ?></p>
        </div>
      <?php endif; ?>
      <!-- Overlay عند hover -->
      <div style="position:absolute;inset:0;background:#00000050;opacity:0;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:16px;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
        <span style="color:#fff;font-size:.8rem;">❤️ <?= $post['like_count'] ?></span>
        <span style="color:#fff;font-size:.8rem;">💬 <?= $post['comment_count'] ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</main>

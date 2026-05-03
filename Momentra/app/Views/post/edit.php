<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">Edit Post</h2>
  <form method="POST" action="<?= BASE_URL ?>/post/edit?id=<?= $post['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <?php if ($post['image']): ?>
    <img src="<?= postImageUrl($post['image']) ?>" style="width:100%;border-radius:10px;margin-bottom:16px;max-height:400px;object-fit:cover;">
    <?php endif; ?>
    <div class="form-group">
      <label>Caption</label>
      <textarea name="caption"><?= h($post['caption'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" value="<?= h($post['location'] ?? '') ?>" placeholder="Add location…">
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <a href="<?= BASE_URL ?>/post?id=<?= $post['id'] ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </form>
</div>
</main>

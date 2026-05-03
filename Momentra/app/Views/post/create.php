<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">New Post</h2>

  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= h($err) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Tab selector -->
  <div style="display:flex;gap:8px;margin-bottom:24px;">
    <button type="button" onclick="switchTab('photo')" id="tab-photo" class="btn btn-primary btn-sm">📷 Photo</button>
    <button type="button" onclick="switchTab('text')" id="tab-text" class="btn btn-outline btn-sm">✍️ Text</button>
  </div>

  <form method="POST" action="<?= BASE_URL ?>/post/create" enctype="multipart/form-data" id="create-form">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="post_type" id="post-type" value="photo">
    <input type="hidden" name="text_bg" id="text-bg-val" value="gradient-purple">

    <!-- Photo tab -->
     <div id="section-photo">
      <div id="drop-zone" onclick="document.getElementById('img-input').click()" style="border:2px dashed var(--border);border-radius:14px;aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:border .2s;margin-bottom:20px;position:relative;overflow:hidden;background:var(--surface);">
        <div id="drop-placeholder" style="text-align:center;padding:32px;">
          <div style="font-size:2.5rem;margin-bottom:12px;">🖼️</div>
          <div style="font-weight:600;margin-bottom:4px;">Drag & drop or click to upload</div>
          <div style="color:var(--muted);font-size:.8rem;">JPG, PNG, GIF, WEBP · max 5MB</div>
        </div>
        <img id="preview" style="display:none;width:100%;height:100%;object-fit:cover;position:absolute;inset:0;" alt="Preview">
      </div>
      <input type="file" id="img-input" name="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
    </div>

    <!-- Text tab -->
    <div id="section-text" style="display:none;">
      <div id="text-preview" style="width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;margin-bottom:16px;padding:32px;">
        <p id="text-preview-p" style="color:#fff;font-size:1.2rem;font-weight:600;text-align:center;line-height:1.5;word-break:break-word;margin:0;">Your text here…</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        <?php
        $bgs = [
          'gradient-purple' => 'linear-gradient(135deg,#6366f1,#8b5cf6)',
          'gradient-pink'   => 'linear-gradient(135deg,#ec4899,#f43f5e)',
          'gradient-blue'   => 'linear-gradient(135deg,#3b82f6,#06b6d4)',
          'gradient-green'  => 'linear-gradient(135deg,#10b981,#84cc16)',
          'gradient-orange' => 'linear-gradient(135deg,#f59e0b,#ef4444)',
          'dark'            => '#111827',
          'light'           => '#f1f5f9',
        ];
        foreach ($bgs as $key => $style): ?>
        <button type="button" onclick="selectBg('<?= $key ?>', '<?= $style ?>')"
          style="width:36px;height:36px;border-radius:50%;background:<?= $style ?>;border:3px solid transparent;cursor:pointer;transition:border-color .2s;" data-bg="<?= $key ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Caption</label>
      <textarea name="caption" id="caption-input" placeholder="Write a caption…" oninput="updatePreview()"><?= h($_POST['caption'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" value="<?= h($_POST['location'] ?? '') ?>" placeholder="Add location…">
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;">
      <a href="<?= BASE_URL ?>/" class="btn btn-outline" style="padding:10px 24px;">Cancel</a>
      <button type="submit" class="btn btn-primary" style="width:auto;padding:10px 32px;">Share Post</button>
    </div>
  </form>
</div>
</main>

<script>
function switchTab(tab) {
  const isPhoto = tab === 'photo';
  document.getElementById('post-type').value = tab;
  document.getElementById('section-photo').style.display = isPhoto ? 'block' : 'none';
  document.getElementById('section-text').style.display  = isPhoto ? 'none' : 'block';
  document.getElementById('tab-photo').className = 'btn btn-sm ' + (isPhoto ? 'btn-primary' : 'btn-outline');
  document.getElementById('tab-text').className  = 'btn btn-sm ' + (isPhoto ? 'btn-outline' : 'btn-primary');
}

function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('preview');
      img.src = e.target.result;
      img.style.display = 'block';
      document.getElementById('drop-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function updatePreview() {
  const text = document.getElementById('caption-input').value || 'Your text here…';
  document.getElementById('text-preview-p').textContent = text;
}

function selectBg(key, style) {
  document.getElementById('text-bg-val').value = key;
  document.getElementById('text-preview').style.background = style;
  const textColor = key === 'light' ? '#111827' : '#ffffff';
  document.getElementById('text-preview-p').style.color = textColor;
  document.querySelectorAll('[data-bg]').forEach(b => b.style.borderColor = b.dataset.bg === key ? '#fff' : 'transparent');
}
</script>

<?php
// ✅ نعمل كل المعالجة قبل أي HTML output
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$me     = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $postType = $_POST['post_type'] ?? 'photo';
    $textBg   = $_POST['text_bg']   ?? 'gradient-purple';
    $result = createPost($me['id'], $_POST['caption'] ?? '', $_POST['location'] ?? '', $_FILES['image'] ?? [], $postType, $textBg);
    if ($result['success']) {
        header('Location: ' . BASE_URL . '/pages/post.php?id=' . $result['post_id']);
        exit;
    }
    $errors = $result['errors'];
}

// ✅ بس بعد كده نعمل include للـ header (HTML)
$pageTitle = 'New Post';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;font-size:1.6rem;">Share a Post</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <!-- Post Type Tabs -->
  <div style="display:flex;gap:8px;margin-bottom:20px;">
    <button id="tab-photo" onclick="switchTab('photo')" class="btn btn-primary btn-sm" style="flex:1;">
      🖼️ Photo + Text
    </button>
    <button id="tab-text" onclick="switchTab('text')" class="btn btn-outline btn-sm" style="flex:1;">
      📝 Text Only
    </button>
  </div>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="post_type" id="post_type" value="photo">

    <!-- Image Drop Zone (shown for photo tab) -->
    <div id="photo-section">
      <div id="drop-zone" onclick="document.getElementById('img-input').click()" style="border:2px dashed var(--border);border-radius:14px;aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:border .2s;margin-bottom:20px;position:relative;overflow:hidden;background:var(--surface);">
        <div id="drop-placeholder" style="text-align:center;padding:32px;">
          <div style="font-size:2.5rem;margin-bottom:12px;">🖼️</div>
          <div style="font-weight:600;margin-bottom:4px;">Drag & drop or click to upload</div>
          <div style="color:var(--muted);font-size:.8rem;">JPG, PNG, GIF, WEBP · max 5MB</div>
        </div>
        <img id="preview" style="display:none;width:100%;height:100%;object-fit:cover;position:absolute;inset:0;" alt="Preview">
      </div>
      <input type="file" id="img-input" name="image" accept="image/*" style="display:none;">
    </div>

    <!-- Text-only background color picker -->
    <div id="text-section" style="display:none;margin-bottom:20px;">
      <div style="margin-bottom:10px;font-size:.8rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Background Color</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php
        $bgColors = [
          'gradient-purple' => 'linear-gradient(135deg,#6366f1,#8b5cf6)',
          'gradient-pink'   => 'linear-gradient(135deg,#ec4899,#f43f5e)',
          'gradient-blue'   => 'linear-gradient(135deg,#3b82f6,#06b6d4)',
          'gradient-green'  => 'linear-gradient(135deg,#10b981,#84cc16)',
          'gradient-orange' => 'linear-gradient(135deg,#f59e0b,#ef4444)',
          'dark'            => '#111827',
          'light'           => '#f1f5f9',
        ];
        foreach ($bgColors as $key => $gradient): ?>
        <button type="button" onclick="selectBg('<?= $key ?>')" data-bg="<?= $key ?>"
          style="width:36px;height:36px;border-radius:50%;border:3px solid transparent;background:<?= $gradient ?>;cursor:pointer;transition:border .2s;"
          title="<?= $key ?>"></button>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="text_bg" id="text_bg" value="gradient-purple">
      <!-- Preview of text post -->
      <div id="text-preview" style="margin-top:16px;border-radius:14px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px;">
        <p id="text-preview-content" style="color:#fff;font-size:1.3rem;font-weight:600;text-align:center;line-height:1.5;word-break:break-word;">Your text will appear here…</p>
      </div>
    </div>

    <div class="form-group">
      <label id="caption-label">Caption</label>
      <textarea name="caption" id="caption-input" 
      placeholder="Write a caption…" 
      oninput="updateTextPreview(this.value)">
      <?= h($_POST['caption'] ?? '') ?>
    </textarea>
    </div>

    <div class="form-group">
      <label>Location (optional)</label>
      <input type="text" name="location" placeholder="Add location…" value="<?= h($_POST['location'] ?? '') ?>">
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        Share Post
      </button>
    </div>
  </form>
</div>
</main>

<script>
const input = document.getElementById('img-input');
const preview = document.getElementById('preview');
const placeholder = document.getElementById('drop-placeholder');
const dropZone = document.getElementById('drop-zone');

// Background gradients map
const bgMap = {
  'gradient-purple': 'linear-gradient(135deg,#6366f1,#8b5cf6)',
  'gradient-pink':   'linear-gradient(135deg,#ec4899,#f43f5e)',
  'gradient-blue':   'linear-gradient(135deg,#3b82f6,#06b6d4)',
  'gradient-green':  'linear-gradient(135deg,#10b981,#84cc16)',
  'gradient-orange': 'linear-gradient(135deg,#f59e0b,#ef4444)',
  'dark':            '#111827',
  'light':           '#f1f5f9',
};
const bgTextColor = { 'light': '#111827' };

function selectBg(key) {
  document.getElementById('text_bg').value = key;
  document.getElementById('text-preview').style.background = bgMap[key];
  const color = bgTextColor[key] || '#ffffff';
  document.getElementById('text-preview-content').style.color = color;
  document.querySelectorAll('[data-bg]').forEach(btn => {
    btn.style.borderColor = btn.dataset.bg === key ? '#fff' : 'transparent';
  });
}
// Select first by default
selectBg('gradient-purple');

function updateTextPreview(val) {
  const el = document.getElementById('text-preview-content');
  el.textContent = val.trim() || 'Your text will appear here…';
}

function switchTab(tab) {
  const isPhoto = tab === 'photo';
  document.getElementById('photo-section').style.display = isPhoto ? 'block' : 'none';
  document.getElementById('text-section').style.display  = isPhoto ? 'none'  : 'block';
  document.getElementById('post_type').value = tab;
  document.getElementById('tab-photo').className = isPhoto ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
  document.getElementById('tab-text').className  = isPhoto ? 'btn btn-outline btn-sm' : 'btn btn-primary btn-sm';
  document.getElementById('caption-label').textContent = isPhoto ? 'Caption' : 'Your Text';
  if (isPhoto) {
    document.getElementById('caption-input').placeholder = 'Write a caption…';
  } else {
    document.getElementById('caption-input').placeholder = 'What\'s on your mind?';
  }
}

function showPreview(file) {
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

input.addEventListener('change', () => { 
  if (input.files[0]) showPreview(input.files[0]); 
});
dropZone.addEventListener('dragover', e => { 
  e.preventDefault(); 
  dropZone.style.borderColor = 'var(--accent)'; 
});
dropZone.addEventListener('dragleave', () => { 
  dropZone.style.borderColor = 'var(--border)'; 
});
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.style.borderColor = 'var(--border)';
  const dt = new DataTransfer();
  dt.items.add(e.dataTransfer.files[0]);
  input.files = dt.files;
  showPreview(e.dataTransfer.files[0]);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">Edit Profile</h2>

  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= h($err) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/profile/edit" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border);">
      <img id="avatar-preview" src="<?= avatarUrl($me['avatar']) ?>" class="avatar" style="width:72px;height:72px;" alt="">
      <div>
        <label class="btn btn-outline btn-sm" style="cursor:pointer;">
          Change Photo
          <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">
        </label>
        <div style="color:var(--muted);font-size:.78rem;margin-top:6px;">JPG, PNG, GIF · max 5MB</div>
      </div>
    </div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?= h($me['username']) ?>" required>
    </div>
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?= h($me['full_name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Bio</label>
      <textarea name="bio"><?= h($me['bio'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Website</label>
      <input type="url" name="website" value="<?= h($me['website'] ?? '') ?>" placeholder="https://…">
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <a href="<?= BASE_URL ?>/profile?u=<?= h($me['username']) ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>

  <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);">
    <h3 style="font-size:1rem;margin-bottom:8px;">🔒 Password</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Update your password to keep your account secure.</p>
    <a href="<?= BASE_URL ?>/change-password" class="btn btn-outline btn-sm">Change Password</a>
  </div>

  <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);">
    <h3 style="font-size:1rem;margin-bottom:8px;color:#ef4444;">⚠️ Danger Zone</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Permanently delete your account and all your data. This cannot be undone.</p>
    <button onclick="document.getElementById('delete-modal').style.display='flex'" class="btn" style="background:#ef444420;border:1px solid #ef444460;color:#f87171;font-size:.875rem;padding:8px 16px;border-radius:8px;cursor:pointer;">
      Delete Account
    </button>
  </div>
</div>
</main>

<!-- Delete Account Modal -->
<div id="delete-modal" style="display:none;position:fixed;inset:0;background:#00000080;z-index:500;align-items:center;justify-content:center;padding:20px;">
  <div class="card" style="max-width:400px;width:100%;padding:28px;">
    <h3 style="margin-bottom:8px;color:#f87171;">Delete Account</h3>
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:20px;">Enter your password to confirm. All your posts, comments and data will be permanently deleted.</p>
    <input type="password" id="delete-pw" placeholder="Your password" style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;margin-bottom:16px;">
    <div style="display:flex;gap:10px;">
      <button onclick="document.getElementById('delete-modal').style.display='none'" class="btn btn-outline" style="flex:1;">Cancel</button>
      <button onclick="confirmDelete()" style="flex:1;padding:11px;border:none;border-radius:10px;background:#ef4444;color:#fff;font-weight:700;cursor:pointer;">Yes, Delete</button>
    </div>
    <div id="delete-err" style="color:#f87171;font-size:.85rem;margin-top:10px;display:none;"></div>
  </div>
</div>
<script>
async function confirmDelete() {
  const pw  = document.getElementById('delete-pw').value;
  const err = document.getElementById('delete-err');
  if (!pw) { err.textContent = 'Please enter your password.'; err.style.display='block'; return; }
  const res  = await fetch('<?= BASE_URL ?>/profile/delete', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'csrf_token=<?= csrfToken() ?>&password=' + encodeURIComponent(pw)
  });
  const data = await res.json();
  if (data.success) {
    window.location.href = data.redirect;
  } else {
    err.textContent = data.error || 'Failed.';
    err.style.display = 'block';
  }
}
</script>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

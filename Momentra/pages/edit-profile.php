<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$errors  = [];
$success = false;
$me      = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $result = updateProfile($me['id'], $_POST, $_FILES['avatar'] ?? null);
    if ($result['success']) {
        $success = true;
        $_SESSION['username'] = $_POST['username'] ?? $me['username'];
    } else {
        $errors = $result['errors'];
    }
}
// Always re-fetch fresh user data
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$me['id']]);
$me = $stmt->fetch();

$pageTitle = 'Edit Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<div class="card" style="padding:28px;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">Edit Profile</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= h($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">Profile updated successfully!</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <!-- Avatar -->
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
      <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($me['username']) ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>

  <!-- Change Password Section -->
  <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);">
    <h3 style="font-size:1rem;margin-bottom:8px;">🔒 Password</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Update your password to keep your account secure.</p>
    <a href="<?= BASE_URL ?>/pages/change-password.php" class="btn btn-outline btn-sm">Change Password</a>
  </div>

  <!-- Delete Account Section -->
  <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border);">
    <h3 style="color:#e74c3c;font-size:1rem;margin-bottom:8px;">⚠️ Danger Zone</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Deleting your account is permanent. All your posts, comments, and data will be erased and cannot be recovered.</p>
    <button onclick="document.getElementById('delete-modal').style.display='flex'" class="btn btn-danger btn-sm">Delete My Account</button>
  </div>
</div>

<!-- Delete Account Modal -->
<div id="delete-modal" style="display:none;position:fixed;inset:0;background:#00000080;z-index:999;align-items:center;justify-content:center;padding:24px;">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:18px;padding:28px;max-width:400px;width:100%;">
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:8px;color:#e74c3c;">Delete Account</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:20px;">This action is <strong>irreversible</strong>. Enter your password to confirm.</p>
    <div class="form-group">
      <label>Your Password</label>
      <div style="position:relative;">
        <input type="password" id="delete-password" placeholder="Enter your password" style="padding-right:44px;">
        <button type="button" onclick="toggleDeletePw()" title="Show / Hide" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);display:flex;align-items:center;padding:4px;transition:color .2s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
          <svg id="del-eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          <svg id="del-eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
        </button>
      </div>
    </div>
    <div id="delete-error" style="display:none;" class="alert alert-error"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
      <button onclick="document.getElementById('delete-modal').style.display='none'" class="btn btn-outline">Cancel</button>
      <button onclick="confirmDelete()" class="btn btn-danger">Yes, Delete My Account</button>
    </div>
  </div>
</div>

</main>
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}

function toggleDeletePw() {
  const input = document.getElementById('delete-password');
  const show  = document.getElementById('del-eye-show');
  const hide  = document.getElementById('del-eye-hide');
  if (input.type === 'password') {
    input.type = 'text';
    show.style.display = 'none';
    hide.style.display = 'block';
  } else {
    input.type = 'password';
    show.style.display = 'block';
    hide.style.display = 'none';
  }
}

async function confirmDelete() {
  const password = document.getElementById('delete-password').value;
  const errBox   = document.getElementById('delete-error');
  if (!password) { 
    errBox.textContent = 'Please enter your password.'; 
    errBox.style.display='block'; 
    return; 
  }

  const res  = await fetch('<?= BASE_URL ?>/api/delete-account.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `password=${encodeURIComponent(password)}&csrf_token=<?= csrfToken() ?>`
  });
  const data = await res.json();

  if (data.success) {
    window.location = '<?= BASE_URL ?>/pages/login.php';
  } else {
    errBox.textContent = data.error || 'Something went wrong.';
    errBox.style.display = 'block';
  }
}

// Close modal on backdrop click
document.getElementById('delete-modal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

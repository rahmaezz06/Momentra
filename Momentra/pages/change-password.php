<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin();

$me      = currentUser();
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password']     ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Fetch stored hash
    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$me['id']]);
    $row = $stmt->fetch();

    if (!password_verify($current, $row['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($new === $current) {
        $errors[] = 'New password must be different from your current password.';
    }
    if ($new !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $me['id']]);
        $success = true;
    }
}

$pageTitle = 'Change Password';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<div class="card" style="padding:28px;max-width:520px;margin:0 auto;">

  <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="<?= BASE_URL ?>/pages/edit-profile.php" style="color:var(--muted);display:flex;align-items:center;text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;">Change Password</h2>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error">⚠️ <?= h($err) ?></div>
  <?php endforeach; ?>

  <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:20px;">
      ✅ Password changed successfully!
    </div>
    <a href="<?= BASE_URL ?>/pages/edit-profile.php" class="btn btn-primary">← Back to Profile Settings</a>

  <?php else: ?>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:24px;line-height:1.6;">
      Choose a strong password. It should be at least 6 characters and different from your current one.
    </p>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <!-- Current Password -->
      <div class="form-group">
        <label>Current Password</label>
        <div style="position:relative;">
          <input type="password" name="current_password" id="pw-current" required placeholder="Your current password" style="padding-right:46px;">
          <button type="button" class="toggle-eye" onclick="togglePw('pw-current','e1s','e1h')" title="Show/Hide">
            <svg id="e1s" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg id="e1h" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
          </button>
        </div>
        <div style="margin-top:5px;">
          <a href="<?= BASE_URL ?>/pages/forgot-password.php" style="font-size:.78rem;color:var(--muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
            Forgot your current password?
          </a>
        </div>
      </div>

      <!-- New Password -->
      <div class="form-group">
        <label>New Password</label>
        <div style="position:relative;">
          <input type="password" name="new_password" id="pw-new" required minlength="6" placeholder="At least 6 characters" style="padding-right:46px;" oninput="checkStrength(this.value)">
          <button type="button" class="toggle-eye" onclick="togglePw('pw-new','e2s','e2h')" title="Show/Hide">
            <svg id="e2s" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg id="e2h" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
          </button>
        </div>
        <!-- Strength bar -->
        <div style="height:4px;border-radius:4px;margin-top:8px;background:var(--border);overflow:hidden;">
          <div id="sfill" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
        </div>
        <div id="slabel" style="color:var(--muted);font-size:.77rem;margin-top:4px;min-height:1.1em;"></div>
      </div>

      <!-- Confirm Password -->
      <div class="form-group">
        <label>Confirm New Password</label>
        <div style="position:relative;">
          <input type="password" name="confirm_password" id="pw-confirm" required minlength="6" placeholder="Repeat new password" style="padding-right:46px;" oninput="checkMatch()">
          <button type="button" class="toggle-eye" onclick="togglePw('pw-confirm','e3s','e3h')" title="Show/Hide">
            <svg id="e3s" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg id="e3h" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
          </button>
        </div>
        <div id="match-label" style="font-size:.77rem;margin-top:4px;min-height:1.1em;"></div>
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
        <a href="<?= BASE_URL ?>/pages/edit-profile.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </div>
    </form>
  <?php endif; ?>
</div>
</main>

<style>
.toggle-eye{
  position:absolute; right:12px; top:50%; transform:translateY(-50%);
  background:none; border:none; cursor:pointer; color:var(--muted);
  display:flex; align-items:center; padding:4px; transition:color .2s;
}
.toggle-eye:hover{ color:var(--text); }
</style>

<script>
function togglePw(inputId, showId, hideId) {
  const input = document.getElementById(inputId);
  const show  = document.getElementById(showId);
  const hide  = document.getElementById(hideId);
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

function checkStrength(val) {
  const fill  = document.getElementById('sfill');
  const label = document.getElementById('slabel');
  if (!fill) return;
  let score = 0;
  if (val.length >= 6)           score++;
  if (val.length >= 10)          score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    {w:'0%',   c:'transparent', t:''},
    {w:'25%',  c:'#ef4444',     t:'Weak'},
    {w:'50%',  c:'#f59e0b',     t:'Fair'},
    {w:'75%',  c:'#6366f1',     t:'Good'},
    {w:'100%', c:'#10b981',     t:'Strong'},
  ];
  const l = levels[Math.min(score, 4)];
  fill.style.width      = l.w;
  fill.style.background = l.c;
  label.textContent     = l.t ? 'Strength: ' + l.t : '';
  label.style.color     = l.c === 'transparent' ? 'var(--muted)' : l.c;
  checkMatch();
}

function checkMatch() {
  const pw1   = document.getElementById('pw-new');
  const pw2   = document.getElementById('pw-confirm');
  const label = document.getElementById('match-label');
  if (!pw2 || !pw2.value) { label.textContent = ''; return; }
  if (pw1.value === pw2.value) {
    label.textContent  = '✓ Passwords match';
    label.style.color  = '#10b981';
  } else {
    label.textContent  = '✗ Passwords do not match';
    label.style.color  = '#ef4444';
  }
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

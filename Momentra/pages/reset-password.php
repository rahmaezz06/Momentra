<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();

// Allow logged-in users to reset password via link
// if (isLoggedIn()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = false;
$valid   = false;
$userId  = null;

// Validate token
if ($token) {
    try {
        // Debug: check token without expiry filter first
        $stmt = db()->prepare('SELECT pr.*, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if (!$row) {
            $error = 'DEBUG: Token not found in DB. Request a new link.';
        } elseif ($row['used'] == 1) {
            $error = 'DEBUG: Token was already used.';
        } else {
            $now = date('Y-m-d H:i:s');
            $exp = $row['expires_at'];
            if ($now > $exp) {
                $error = 'DEBUG: Token expired. expires_at=' . $exp . ' | PHP now=' . $now;
            } else {
                $valid = true; $userId = $row['user_id'];
            }
        }
    } catch (Exception $e) {
        $error = 'DB Error: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $pw  = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($pw) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pw !== $pw2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
        db()->prepare('UPDATE password_resets SET used = 1 WHERE token = ?')->execute([$token]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password · Momentra</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0b1020;
  --surface:#131929;
  --card:#1a2236;
  --border:#2d3a55;
  --text:#f8fafc;
  --muted:#8899bb;
  --accent:#6366f1;
  --accent2:#8b5cf6;
  --gradient:linear-gradient(135deg,#6366f1,#8b5cf6);
}
*,*::before,*::after{
  box-sizing:border-box;
  margin:0;
  padding:0
}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
  background-image:radial-gradient(ellipse at 20% 20%,#6366f118 0%,transparent 55%),
                  radial-gradient(ellipse at 80% 80%,#8b5cf612 0%,transparent 55%);}
.auth-wrap{
  width:100%;
  max-width:420px;
}
.auth-logo{
  text-align:center;
  margin-bottom:32px;
}
.auth-logo h1{
  font-family:'Playfair Display',serif;
  font-size:2.8rem;
  background:var(--gradient);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
.auth-logo p{
  color:var(--muted);
  font-size:.88rem;
  margin-top:6px;
}
.auth-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:20px;
  padding:34px 32px;
  box-shadow:0 24px 60px #00000055;
}
.form-group{
  margin-bottom:18px;
}
.form-group label{
  display:block;
  font-size:.75rem;
  font-weight:600;
  color:var(--muted);
  text-transform:uppercase;
  letter-spacing:.07em;
  margin-bottom:7px;
}
.input-wrap{
  position:relative;
}
.input-wrap input{
  width:100%;
  background:var(--surface);
  border:1.5px solid var(--border);
  border-radius:10px;
  padding:12px 46px 12px 14px;
  color:var(--text);
  font-family:inherit;
  font-size:.95rem;
  outline:none;
  transition:border-color .2s,box-shadow .2s;
}
.input-wrap input:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px #6366f122;
}
.toggle-pw{
  position:absolute;
  right:12px;top:50%;
  transform:translateY(-50%);
  background:none;
  border:none;
  cursor:pointer;
  color:var(--muted);
  display:flex;
  align-items:center;
  padding:4px;
  transition:color .2s;
}
.toggle-pw:hover{
  color:var(--text);
}
.btn-primary{
  display:block;
  width:100%;
  padding:13px;
  border:none;
  border-radius:10px;
  background:var(--gradient);
  color:#fff;
  font-family:inherit;
  font-size:1rem;
  font-weight:700;
  cursor:pointer;
  transition:opacity .2s,transform .15s;
}
.btn-primary:hover{
  opacity:.87;
  transform:translateY(-1px);
}
.alert-error{
  background:#6366f115;
  border:1px solid #ef444440;
  color:#fca5a5;
  padding:12px 16px;
  border-radius:10px;
  margin-bottom:18px;
  font-size:.875rem;
}
.alert-success{
  background:#10b98115;
  border:1px solid #10b98140;
  color:#6ee7b7;
  padding:16px;
  border-radius:10px;
  font-size:.9rem;
  line-height:1.6;
}
.alert-success a{
  color:#a5b4fc;
  font-weight:600;
  text-decoration:none;
}
.back-link{
  display:flex;
  align-items:center;
  gap:6px;
  color:var(--muted);
  text-decoration:none;
  font-size:.875rem;
  margin-bottom:22px;
  transition:color .2s;
}
.back-link:hover{
  color:var(--text);
}
.strength-bar{
  height:4px;
  border-radius:4px;
  margin-top:6px;
  background:var(--border);
  overflow:hidden;
}
.strength-fill{
  height:100%;
  width:0;
  border-radius:4px;
  transition:width .3s,background .3s;
}
.hint{
  color:var(--muted);
  font-size:.77rem;
  margin-top:4px;
  }
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">
    <h1>Momentra</h1>
    <p>Choose a new password</p>
  </div>
  <div class="auth-card">
    <a href="<?= BASE_URL ?>/pages/login.php" class="back-link">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Back to Login
    </a>

    <?php if ($error): ?>
      <div class="alert-error">⚠️ <?= h($error) ?></div>
      <br>
      <a href="<?= BASE_URL ?>/pages/forgot-password.php" class="btn-primary" style="display:block;text-align:center;text-decoration:none;">← Request a New Link</a>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-success">
        ✅ Password updated successfully!<br><br>
        <a href="<?= BASE_URL ?>/pages/login.php">→ Go to Login</a>
      </div>
    <?php elseif ($valid): ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <div class="form-group">
          <label>New Password</label>
          <div class="input-wrap">
            <input type="password" name="password" id="pw1" required minlength="6" placeholder="At least 6 characters" oninput="checkStrength(this.value)">
            <button type="button" class="toggle-pw" onclick="togglePw('pw1','s1','h1')">
              <svg id="s1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg id="h1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
            </button>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="sfill"></div></div>
          <div class="hint" id="slabel"></div>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <div class="input-wrap">
            <input type="password" name="password2" id="pw2" required minlength="6" placeholder="Repeat password">
            <button type="button" class="toggle-pw" onclick="togglePw('pw2','s2','h2')">
              <svg id="s2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg id="h2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary">Reset Password</button>
      </form>
    <?php elseif (!$token): ?>
      <p style="color:var(--muted);font-size:.9rem;">No reset token provided. <a href="<?= BASE_URL ?>/pages/forgot-password.php" style="color:var(--accent2);">Request a new link</a>.</p>
    <?php endif; ?>
  </div>
</div>
<script>
function togglePw(id, showId, hideId) {
  const f = document.getElementById(id);
  const show = document.getElementById(showId);
  const hide = document.getElementById(hideId);
  if (f.type === 'password') { f.type='text'; show.style.display='none'; hide.style.display='block'; }
  else { f.type='password'; show.style.display='block'; hide.style.display='none'; }
}
function checkStrength(val) {
  const fill = document.getElementById('sfill');
  const label = document.getElementById('slabel');
  let score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels=[{w:'0%',c:'transparent',t:''},{w:'25%',c:'#ef4444',t:'Weak'},{w:'50%',c:'#f59e0b',t:'Fair'},{w:'75%',c:'#6366f1',t:'Good'},{w:'100%',c:'#10b981',t:'Strong'}];
  const l = levels[Math.min(score,4)];
  fill.style.width=l.w; fill.style.background=l.c;
  label.textContent = l.t ? 'Password strength: ' + l.t : '';
}
</script>
</body>
</html>
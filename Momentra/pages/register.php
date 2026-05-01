<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();

if (isLoggedIn()) { 
  header('Location: ' . BASE_URL . '/index.php'); 
  exit; 
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = register(
        $_POST['username'] ?? '',
        $_POST['email']    ?? '',
        $_POST['password'] ?? '',
        $_POST['full_name'] ?? ''
    );
    if ($result['success']) {
        login($_POST['email'], $_POST['password']);
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up · Momentra</title>
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
  --radius:14px;
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
                   radial-gradient(ellipse at 80% 80%,#8b5cf612 0%,transparent 55%);
}
.auth-wrap{
  width:100%;
  max-width:420px;
}
.auth-logo{
  text-align:center;
  margin-bottom:30px;
}
.auth-logo h1{
  font-family:'Playfair Display',serif;
  font-size:2.8rem;
  background:var(--gradient);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  letter-spacing:-.5px;
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
  padding:32px;
  box-shadow:0 24px 60px #00000055;
}
.form-group{
  margin-bottom:16px;
}
.form-group label{
  display:block;
  font-size:.75rem;
  font-weight:600;
  color:var(--muted);
  text-transform:uppercase;
  letter-spacing:.07em;
  margin-bottom:6px;
}
.input-wrap{
  position:relative;
}
.input-wrap input{
  width:100%;
  background:var(--surface);
  border:1.5px solid var(--border);
  border-radius:10px;
  padding:11px 46px 11px 14px;
  color:var(--text);
  font-family:inherit;
  font-size:.93rem;
  outline:none;
  transition:border-color .2s,box-shadow .2s;
}
.input-wrap input.no-icon{
  padding-right:14px;
}
.input-wrap input:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px #6366f122;
}
.toggle-pw{
  position:absolute;
  right:12px;
  top:50%;
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
  transition:opacity .2s,transform .15s;margin-top:10px;
}
.btn-primary:hover{
  opacity:.87;
  transform:translateY(-1px);
}
.alert-error{
  background:#6366f115;
  border:1px solid #6366f140;
  color:#a5b4fc;
  padding:11px 16px;
  border-radius:10px;
  margin-bottom:16px;
  font-size:.85rem;
}
.hint{
  color:var(--muted);
  font-size:.77rem;
  margin-top:4px;
}
.auth-footer{
  text-align:center;
  margin-top:22px;
  color:var(--muted);
  font-size:.875rem;
}
.auth-footer a{
  color:var(--accent2);
  text-decoration:none;
  font-weight:600;
  transition:color .2s;
}
.auth-footer a:hover{
  color:var(--text);
}
/* strength bar */
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
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">
    <h1>Momentra</h1>
    <p>Create your account and start sharing moments</p>
  </div>
  <div class="auth-card">
    <?php foreach ($errors as $err): ?>
      <div class="alert-error">⚠️ <?= h($err) ?></div>
    <?php endforeach; ?>
    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <div class="input-wrap">
          <input type="text" name="full_name" class="no-icon" value="<?= h($_POST['full_name'] ?? '') ?>" placeholder="Your full name">
        </div>
      </div>
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <input type="text" name="username" class="no-icon" value="<?= h($_POST['username'] ?? '') ?>" required autofocus placeholder="Choose a username">
        </div>
      </div>
      <div class="form-group">
        <label>Email</label>
        <div class="input-wrap">
          <input type="email" name="email" class="no-icon" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="pw-reg" required minlength="6" placeholder="At least 6 characters" oninput="checkStrength(this.value)">
          <button type="button" class="toggle-pw" onclick="togglePwReg()" title="Show / Hide password">
            <svg id="reg-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg id="reg-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
          </button>
        </div>
        <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
        <div class="hint" id="strength-label"></div>
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>
    <div class="auth-footer" style="margin-top:20px;">Already have an account? <a href="<?= BASE_URL ?>/pages/login.php">Log in</a></div>
  </div>
</div>
<script>
function togglePwReg() {
  const f = document.getElementById('pw-reg');
  const show = document.getElementById('reg-show');
  const hide = document.getElementById('reg-hide');
  if (f.type === 'password') { 
    f.type = 'text'; 
    show.style.display='none'; 
    hide.style.display='block'; 
  }
  else { 
    f.type = 'password'; 
    show.style.display='block'; 
    hide.style.display='none'; 
  }
}
function checkStrength(val) {
  const fill = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');
  let score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    {w:'0%',  c:'transparent', t:''},
    {w:'25%', c:'#ef4444',     t:'Weak'},
    {w:'50%', c:'#f59e0b',     t:'Fair'},
    {w:'75%', c:'#6366f1',     t:'Good'},
    {w:'100%',c:'#10b981',     t:'Strong'},
  ];
  const l = levels[Math.min(score, 4)];
  fill.style.width = l.w; fill.style.background = l.c;
  label.textContent = l.t ? 'Password strength: ' + l.t : '';
}
</script>
</body>
</html>

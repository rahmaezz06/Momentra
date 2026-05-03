<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login · Momentra</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0b1020;--surface:#131929;--card:#1a2236;--border:#2d3a55;--text:#f8fafc;--muted:#8899bb;--accent:#6366f1;--accent2:#8b5cf6;--gradient:linear-gradient(135deg,#6366f1,#8b5cf6);--radius:14px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background-image:radial-gradient(ellipse at 20% 20%,#6366f118,transparent 55%),radial-gradient(ellipse at 80% 80%,#8b5cf612,transparent 55%);}
.auth-wrap{width:100%;max-width:420px;}
.auth-logo{text-align:center;margin-bottom:36px;}
.auth-logo h1{font-family:'Playfair Display',serif;font-size:3rem;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.auth-logo p{color:var(--muted);font-size:.9rem;margin-top:6px;}
.auth-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:36px 32px;box-shadow:0 24px 60px #00000055;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;}
.input-wrap{position:relative;}
.input-wrap input{width:100%;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;padding:12px 46px 12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;transition:border-color .2s,box-shadow .2s;}
.input-wrap input:focus{border-color:var(--accent);box-shadow:0 0 0 3px #6366f122;}
.toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);display:flex;align-items:center;padding:4px;}
.forgot-link{display:block;text-align:right;font-size:.8rem;color:var(--accent2);text-decoration:none;margin-top:6px;}
.btn-primary{display:block;width:100%;padding:13px;border:none;border-radius:10px;background:var(--gradient);color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;margin-top:8px;}
.btn-primary:hover{opacity:.87;}
.alert-error{background:#6366f115;border:1px solid #6366f140;color:#a5b4fc;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.875rem;}
.divider{display:flex;align-items:center;gap:12px;margin:22px 0;color:var(--muted);font-size:.8rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.auth-footer{text-align:center;margin-top:22px;color:var(--muted);font-size:.875rem;}
.auth-footer a{color:var(--accent2);text-decoration:none;font-weight:600;}
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">
    <h1>Momentra</h1>
    <p>Sign in to see moments from your friends</p>
  </div>
  <div class="auth-card">
    <?php if (!empty($error)): ?>
      <div class="alert-error">⚠️ <?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/login">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label>Email</label>
        <div class="input-wrap">
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autofocus placeholder="you@example.com">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="pw-field" required placeholder="••••••••">
          <button type="button" class="toggle-pw" onclick="togglePw()">
            <svg id="icon-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg id="icon-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 2.063-3.366M6.22 6.22A9.97 9.97 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 0 1-4.293 5.411M3 3l18 18"/></svg>
          </button>
        </div>
        <a href="<?= BASE_URL ?>/forgot-password" class="forgot-link">Forgot password?</a>
      </div>
      <button type="submit" class="btn-primary">Log In</button>
    </form>
    <div class="divider">or</div>
    <div class="auth-footer">Don't have an account? <a href="<?= BASE_URL ?>/register">Sign up</a></div>
  </div>
</div>
<script>
function togglePw() {
  const f = document.getElementById('pw-field');
  const show = document.getElementById('icon-show');
  const hide = document.getElementById('icon-hide');
  if (f.type === 'password') { f.type='text'; show.style.display='none'; hide.style.display='block'; }
  else { f.type='password'; show.style.display='block'; hide.style.display='none'; }
}
</script>
</body>
</html>

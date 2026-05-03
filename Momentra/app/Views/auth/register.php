<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up · Momentra</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0b1020;--surface:#131929;--card:#1a2236;--border:#2d3a55;--text:#f8fafc;--muted:#8899bb;--accent:#6366f1;--accent2:#8b5cf6;--gradient:linear-gradient(135deg,#6366f1,#8b5cf6);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background-image:radial-gradient(ellipse at 20% 20%,#6366f118,transparent 55%),radial-gradient(ellipse at 80% 80%,#8b5cf612,transparent 55%);}
.auth-wrap{width:100%;max-width:420px;}
.auth-logo{text-align:center;margin-bottom:30px;}
.auth-logo h1{font-family:'Playfair Display',serif;font-size:2.8rem;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.auth-logo p{color:var(--muted);font-size:.88rem;margin-top:6px;}
.auth-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:32px;box-shadow:0 24px 60px #00000055;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;}
.form-group input{width:100%;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;transition:border-color .2s,box-shadow .2s;}
.form-group input:focus{border-color:var(--accent);box-shadow:0 0 0 3px #6366f122;}
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
    <p>Join and share your moments</p>
  </div>
  <div class="auth-card">
    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $err): ?>
        <div class="alert-error">⚠️ <?= h($err) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/register">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= h($_POST['full_name'] ?? '') ?>" placeholder="Your Name">
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= h($_POST['username'] ?? '') ?>" required placeholder="username">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters">
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>
    <div class="divider">or</div>
    <div class="auth-footer">Already have an account? <a href="<?= BASE_URL ?>/login">Log in</a></div>
  </div>
</div>
</body>
</html>

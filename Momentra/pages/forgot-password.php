<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();

// Allow logged-in users to access forgot-password (e.g. from change-password page)
// if (isLoggedIn()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT id, username FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $stmt = db()->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $stmt->execute([$user['id']]);

            $stmt = db()->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expires]);

            $resetLink = BASE_URL . '/pages/reset-password.php?token=' . $token;

            // TODO: In production, send email instead:
            // mail($email, 'Reset your Momentra password', "Reset link: $resetLink");

            $message = 'reset_link:' . $resetLink; // Dev mode
        } else {
            $message = 'sent'; // Don't reveal if email exists
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password · Momentra</title>
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
  background-image: radial-gradient(ellipse at 20% 20%,#6366f118 0%,transparent 55%),
                    radial-gradient(ellipse at 80% 80%,#8b5cf612 0%,transparent 55%);
}
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
.form-group input{
  width:100%; 
  background:var(--surface); 
  border:1.5px solid var(--border);
  border-radius:10px; 
  padding:12px 14px; 
  color:var(--text); 
  font-family:inherit;
  font-size:.95rem; 
  outline:none; 
  transition:border-color .2s,box-shadow .2s;
}
.form-group input:focus{ 
  border-color:var(--accent); 
  box-shadow:0 0 0 3px #6366f122; 
}
.btn-primary{
  display:flex; 
  align-items:center; 
  justify-content:center; 
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
  text-decoration:none;
}
.btn-primary:hover{ 
  opacity:.87; 
  transform:translateY(-1px); 
}
.btn-primary:disabled{ 
  opacity:.5; 
  cursor:not-allowed; 
  transform:none; 
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
  margin-bottom:18px; 
  font-size:.875rem; 
  line-height:1.6;
}
.alert-dev{
  background:#f59e0b10; 
  border:1px solid #f59e0b35; 
  color:#fcd34d;
  padding:14px 16px; 
  border-radius:10px; 
  margin-top:12px; 
  font-size:.82rem; 
  line-height:1.7;
}
.alert-dev strong{ 
  display:block; 
  margin-bottom:6px; 
  font-size:.78rem; 
  text-transform:uppercase; 
  letter-spacing:.06em; 
  opacity:.65; 
}
.alert-dev a{ 
  color:#a5b4fc; 
  font-weight:600; 
  word-break:break-all; 
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
.spinner{
  display:none; 
  width:16px; 
  height:16px; 
  border:2px solid #ffffff44;
  border-top-color:#fff; 
  border-radius:50%; 
  animation:spin .6s linear infinite; 
  margin-right:8px;
}
@keyframes spin{ 
  to{ 
    transform:rotate(360deg) 
    } 
}
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">
    <h1>Momentra</h1>
    <p>Reset your password</p>
  </div>
  <div class="auth-card">
    <a href="<?= BASE_URL ?>/pages/login.php" class="back-link">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Back to Login
    </a>

    <?php if ($error): ?>
      <div class="alert-error">⚠️ <?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($message === 'sent'): ?>
      <div class="alert-success">
        ✅ If that email is registered, a reset link has been sent.<br><br>
        <small style="color:var(--muted);font-size:.82rem;">Check your inbox and spam folder. The link expires in 1 hour.</small>
      </div>
      <a href="<?= BASE_URL ?>/pages/login.php" class="btn-primary">← Back to Login</a>

    <?php elseif (str_starts_with($message, 'reset_link:')): ?>
      <?php $link = substr($message, 11); ?>
      <div class="alert-success">
        ✅ Reset link generated!<br><br>
        <small style="color:var(--muted);font-size:.82rem;">This link will expire in 1 hour.</small>
      </div>
      <div class="alert-dev">
        <strong>🛠 Dev Mode — Email not configured</strong>
        In production this link would be sent via email. For now, use it directly:<br><br>
        <a href="<?= h($link) ?>">🔗 Click here to reset your password →</a>
      </div>

    <?php else: ?>
      <p style="color:var(--muted);font-size:.9rem;margin-bottom:22px;line-height:1.6;">
        Enter the email address linked to your account and we'll send you a reset link.
      </p>
      <form method="POST" onsubmit="this.querySelector('button').disabled=true;this.querySelector('.spinner').style.display='inline-block';">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autofocus placeholder="you@example.com">
        </div>
        <button type="submit" class="btn-primary">
          <span class="spinner"></span>
          Send Reset Link
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>

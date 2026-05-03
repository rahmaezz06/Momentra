<div class="auth-wrap" style="max-width:420px;margin:calc(var(--nav-h) + 40px) auto 40px;padding:0 16px;">
  <div style="text-align:center;margin-bottom:28px;">
    <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Forgot Password</h1>
    <p style="color:var(--muted);font-size:.9rem;margin-top:6px;">Enter your email to receive a reset link</p>
  </div>
  <div class="card" style="padding:32px;">

    <?php if (!empty($error)): ?>
      <div style="background:#ef444420;border:1px solid #ef444460;color:#fca5a5;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.875rem;">⚠️ <?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($message === 'sent'): ?>
      <div style="background:#10b98120;border:1px solid #10b98160;color:#6ee7b7;padding:14px 16px;border-radius:10px;margin-bottom:20px;">
        ✅ If that email is registered, a reset link has been sent. Check your inbox and spam folder.
      </div>
      <a href="<?= BASE_URL ?>/login" style="display:block;text-align:center;color:var(--accent);font-size:.9rem;">← Back to Login</a>

    <?php elseif (str_starts_with($message ?? '', 'reset_link:')): ?>
      <?php $link = substr($message, 11); ?>
      <div style="background:#10b98120;border:1px solid #10b98160;color:#6ee7b7;padding:14px 16px;border-radius:10px;margin-bottom:16px;">✅ Reset link generated (dev mode — no email configured).</div>
      <div style="background:var(--hover-bg);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:.85rem;line-height:1.6;">
        <strong>🛠 Dev Mode</strong><br>
        <a href="<?= h($link) ?>" style="color:var(--accent);word-break:break-all;">Click here to reset your password →</a>
      </div>
      <a href="<?= BASE_URL ?>/login" style="display:block;text-align:center;color:var(--accent);font-size:.9rem;">← Back to Login</a>

    <?php else: ?>
      <form method="POST" action="<?= BASE_URL ?>/forgot-password">
        <div style="margin-bottom:18px;">
          <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">Email Address</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autofocus placeholder="you@example.com"
            style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;border:none;border-radius:10px;background:var(--gradient);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;">
          Send Reset Link
        </button>
      </form>
      <div style="text-align:center;margin-top:20px;font-size:.875rem;color:var(--muted);">
        Remembered it? <a href="<?= BASE_URL ?>/login" style="color:var(--accent);">Log in</a>
      </div>
    <?php endif; ?>

  </div>
</div>

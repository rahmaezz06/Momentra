<div style="max-width:420px;margin:calc(var(--nav-h) + 40px) auto 40px;padding:0 16px;">
  <div style="text-align:center;margin-bottom:28px;">
    <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Reset Password</h1>
  </div>
  <div class="card" style="padding:32px;">

    <?php if (!empty($error)): ?>
      <div style="background:#ef444420;border:1px solid #ef444460;color:#fca5a5;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.875rem;">⚠️ <?= h($error) ?></div>
      <a href="<?= BASE_URL ?>/forgot-password" style="display:block;text-align:center;color:var(--accent);font-size:.9rem;">Request a new link</a>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div style="background:#10b98120;border:1px solid #10b98160;color:#6ee7b7;padding:14px 16px;border-radius:10px;margin-bottom:20px;">
        ✅ Your password has been reset successfully!
      </div>
      <a href="<?= BASE_URL ?>/login" class="btn btn-primary" style="display:block;text-align:center;padding:13px;border-radius:10px;background:var(--gradient);color:#fff;font-weight:700;text-decoration:none;">
        Log In Now
      </a>
    <?php elseif (!empty($valid)): ?>
      <form method="POST" action="<?= BASE_URL ?>/reset-password">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <div style="margin-bottom:16px;">
          <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">New Password</label>
          <input type="password" name="password" required minlength="6" autofocus
            style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
        </div>
        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">Confirm New Password</label>
          <input type="password" name="password2" required minlength="6"
            style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
        </div>
        <button type="submit" style="width:100%;padding:13px;border:none;border-radius:10px;background:var(--gradient);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;">
          Set New Password
        </button>
      </form>
    <?php endif; ?>

  </div>
</div>

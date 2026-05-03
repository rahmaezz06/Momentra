<main>
<div class="card" style="padding:28px;max-width:480px;margin:0 auto;">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;">Change Password</h2>

  <?php if (!empty($errors)): ?>
    <div style="background:#ef444420;border:1px solid #ef444460;color:#fca5a5;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.875rem;">
      <?php foreach ($errors as $e): ?><div>⚠️ <?= h($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div style="background:#10b98120;border:1px solid #10b98160;color:#6ee7b7;padding:14px 16px;border-radius:10px;margin-bottom:20px;">
      ✅ Password changed successfully!
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/change-password">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">Current Password</label>
      <input type="password" name="current_password" required
        style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">New Password</label>
      <input type="password" name="new_password" required minlength="6"
        style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
    </div>
    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">Confirm New Password</label>
      <input type="password" name="confirm_password" required minlength="6"
        style="width:100%;background:var(--hover-bg);border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;">
    </div>

    <div style="display:flex;gap:12px;align-items:center;">
      <button type="submit" class="btn btn-primary">Update Password</button>
      <a href="<?= BASE_URL ?>/profile?u=<?= h($me['username'] ?? '') ?>" style="color:var(--muted);font-size:.9rem;">Cancel</a>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
      <a href="<?= BASE_URL ?>/forgot-password" style="color:var(--accent);font-size:.85rem;">🔑 Forgot your current password?</a>
    </div>
  </form>
</div>
</main>

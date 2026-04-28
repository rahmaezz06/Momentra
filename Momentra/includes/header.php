<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
startSession();
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $_COOKIE['theme'] ?? 'dark' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= h($pageTitle ?? APP_NAME) ?> · <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
:root {
  --nav-h:    60px;
  --radius:   14px;
  --gradient: linear-gradient(135deg,#6366f1,#8b5cf6);
  --bg:       #f8fafc;
  --surface:  #ffffff;
  --card:     #ffffff;
  --border:   #e2e8f0;
  --text:     #0f172a;
  --muted:    #64748b;
  --accent:   #6366f1;
  --accent2:  #8b5cf6;
  --hover-bg: #f1f5f9;
}
[data-theme="dark"] {
  --bg:       #0b1020;
  --surface:  #1a1a2e;
  --card:     #1a1a2e;
  --border:   #2d2d5e;
  --text:     #f8fafc;
  --muted:    #a5b4fc;
  --accent:   #a78bfa;
  --accent2:  #c4b5fd;
  --hover-bg: #2e2a5e;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);color:var(--text);
  min-height:100vh;
  padding-bottom:80px;
  transition:background-color .3s,color .2s;
}

/* ── NAV ── */
nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  height:var(--nav-h);
  background:var(--surface);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 16px;
  gap:8px;
}
.nav-logo{
  font-family:'Playfair Display',serif;
  font-size:1.4rem;
  background:var(--gradient);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  text-decoration:none;
  flex-shrink:0;
}
.nav-links{display:flex;align-items:center;gap:4px;flex-shrink:0;}
.nav-links a{
  display:flex;align-items:center;gap:6px;
  color:var(--muted);text-decoration:none;
  padding:8px 10px;border-radius:10px;
  font-size:.875rem;font-weight:500;
  transition:all .2s;white-space:nowrap;
}
.nav-links a:hover,.nav-links a.active{color:var(--text);background:var(--border);}
.nav-links a svg{width:20px;height:20px;flex-shrink:0;}

/* ── SEARCH ── */
.nav-search{position:relative;flex:1;max-width:260px;}
.nav-search input{
  width:100%;
  background:var(--hover-bg);
  border:1.5px solid var(--border);
  border-radius:22px;
  padding:8px 14px 8px 38px;
  color:var(--text);font-size:.85rem;
  outline:none;
  transition:border .2s,box-shadow .2s;
}
.nav-search input:focus{border-color:var(--accent);box-shadow:0 0 0 3px #6366f120;}
.nav-search .search-icon{
  position:absolute;left:11px;top:50%;transform:translateY(-50%);
  color:var(--muted);width:16px;height:16px;pointer-events:none;
}
#search-results{
  position:absolute;top:46px;left:0;right:0;
  background:var(--card);border:1px solid var(--border);
  border-radius:var(--radius);overflow:hidden;
  display:none;z-index:200;
  box-shadow:0 8px 32px #00000050;
  max-height:320px;overflow-y:auto;
}
#search-results a{display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:var(--text);transition:background .15s;}
#search-results a:hover{background:var(--hover-bg);}
#search-results img{width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;}
.sr-name{font-size:.85rem;font-weight:500;}
.sr-user{font-size:.75rem;color:var(--muted);}

/* ── AVATAR ── */
.avatar{border-radius:50%;object-fit:cover;border:2px solid var(--border);}
.avatar-ring{border-color:var(--accent)!important;}

/* ── LAYOUT ── */
main{max-width:640px;margin:0 auto;padding:calc(var(--nav-h) + 20px) 12px 20px;}
.wide-main{max-width:960px;margin:0 auto;padding:calc(var(--nav-h) + 20px) 12px 20px;}

/* ── CARD ── */
.card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  overflow:hidden;
  margin-bottom:16px;
}

/* ── BUTTONS ── */
.btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 18px;border-radius:10px;
  font-family:inherit;font-size:.875rem;font-weight:600;
  cursor:pointer;border:none;text-decoration:none;
  transition:all .2s;white-space:nowrap;
}
.btn-primary{background:var(--gradient);color:#fff;}
.btn-primary:hover{opacity:.85;transform:translateY(-1px);}
.btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.btn-ghost{background:transparent;color:var(--muted);padding:6px;}
.btn-ghost:hover{color:var(--text);}
.btn-danger{background:#e53e3e;color:#fff;}
.btn-danger:hover{background:#fc8181;}
.btn-sm{padding:6px 14px;font-size:.8rem;}

/* ── FORMS ── */
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;}
.form-group input,.form-group textarea,.form-group select{
  width:100%;
  background:var(--surface);
  border:1.5px solid var(--border);
  border-radius:10px;
  padding:11px 14px;
  color:var(--text);font-family:inherit;font-size:.9rem;
  outline:none;resize:vertical;
  transition:border .2s,box-shadow .2s;
  -webkit-appearance:none;
}
.form-group input:focus,.form-group textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px #6366f118;}
.form-group textarea{min-height:80px;}

/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.875rem;}
.alert-error{background:#ef444415;border:1px solid #ef444440;color:#fca5a5;}
.alert-success{background:#10b98115;border:1px solid #10b98140;color:#6ee7b7;}

/* ── BOTTOM NAV ── */
.bottom-nav{
  display:none;
  position:fixed;bottom:0;left:0;right:0;
  background:var(--surface);border-top:1px solid var(--border);
  padding:6px 0 max(6px,env(safe-area-inset-bottom));
  z-index:100;
  justify-content:space-around;
  align-items:center;
}
.bottom-nav a{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  color:var(--muted);text-decoration:none;
  font-size:.6rem;font-weight:500;
  padding:4px 16px;border-radius:10px;
  transition:color .2s;
  min-width:60px;
}
.bottom-nav a svg{width:24px;height:24px;}
.bottom-nav a.active,.bottom-nav a:hover{color:var(--accent);}

/* ── THEME TOGGLE ── */
.theme-toggle{
  position:fixed;bottom:72px;right:16px;z-index:99;
  background:var(--card);border:1px solid var(--border);
  border-radius:40px;padding:6px;
  display:flex;gap:2px;
  box-shadow:0 4px 16px #0000003a;
}
.theme-btn{
  background:transparent;border:none;border-radius:32px;
  padding:6px 10px;cursor:pointer;font-size:1rem;
  transition:all .2s;color:var(--muted);
}
.theme-btn.active{background:var(--accent);color:#fff;box-shadow:0 2px 8px #6366f140;}
.theme-btn:hover:not(.active){background:var(--hover-bg);color:var(--text);}

/* ── MOBILE ── */
@media(max-width:640px){
  nav{padding:0 12px;gap:6px;}
  .nav-logo{font-size:1.25rem;}
  .nav-links{display:none;}
  .nav-links.auth-links{display:flex;} /* always show login/signup */
  .nav-search{max-width:none;flex:1;}
  .nav-search input{font-size:.8rem;padding:7px 12px 7px 34px;}
  .bottom-nav{display:flex;}
  .theme-toggle{bottom:72px;right:12px;}
  main{padding:calc(var(--nav-h) + 12px) 8px 12px;}
  .wide-main{padding:calc(var(--nav-h) + 12px) 8px 12px;}
  .card{border-radius:12px;margin-bottom:12px;}
  .btn{padding:8px 14px;}
  .btn-sm{padding:5px 12px;font-size:.78rem;}
}
@media(max-width:400px){
  .nav-search input{font-size:.75rem;}
}
</style>
</head>
<body>

<nav>
  <a href="<?= BASE_URL ?>/index.php" class="nav-logo">Momentra</a>

  <?php if ($me): ?>
  <div class="nav-search">
    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
    <input type="text" id="search-input" placeholder="Search…" autocomplete="off" inputmode="search">
    <div id="search-results"></div>
  </div>
  <?php endif; ?>

  <div class="nav-links<?= !$me ? ' auth-links' : '' ?>">
    <?php if ($me): ?>
      <a href="<?= BASE_URL ?>/index.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V21a.75.75 0 0 1-.75.75H15v-6H9v6H3.75A.75.75 0 0 1 3 21V9.75z"/></svg>
        Home
      </a>
      <a href="<?= BASE_URL ?>/pages/create.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Post
      </a>
      <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($me['username']) ?>">
        <img src="<?= avatarUrl($me['avatar']) ?>" class="avatar" width="26" height="26" alt="">
        <?= h($me['username']) ?>
      </a>
      <a href="<?= BASE_URL ?>/pages/logout.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1"/></svg>
        Logout
      </a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/pages/login.php" class="btn btn-outline btn-sm">Login</a>
      <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-primary btn-sm">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<?php if ($me): ?>
<!-- Bottom Nav -->
<div class="bottom-nav">
  <a href="<?= BASE_URL ?>/index.php">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V21a.75.75 0 0 1-.75.75H15v-6H9v6H3.75A.75.75 0 0 1 3 21V9.75z"/></svg>
    Home
  </a>
  <a href="<?= BASE_URL ?>/pages/create.php">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    New
  </a>
  <a href="<?= BASE_URL ?>/pages/profile.php?u=<?= h($me['username']) ?>">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A10 10 0 1 1 18.88 6.196M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
    Profile
  </a>
  <a href="<?= BASE_URL ?>/pages/logout.php">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2v1"/></svg>
    Logout
  </a>
</div>

<script>
// Live search
const searchInput   = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
let searchTimer;

searchInput?.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const q = searchInput.value.trim();
  if (!q) { searchResults.style.display = 'none'; return; }
  searchTimer = setTimeout(async () => {
    try {
      const res   = await fetch('<?= BASE_URL ?>/api/search.php?q=' + encodeURIComponent(q));
      const users = await res.json();
      if (!users.length) { 
      searchResults.style.display = 'none'; 
      return; 
    }
      searchResults.innerHTML = users.map(u => `
        <a href="<?= BASE_URL ?>/pages/profile.php?u=${encodeURIComponent(u.username)}">
          <img src="${u.avatar_url}" width="36" height="36" alt="">
          <div>
            <div class="sr-name">${u.full_name || u.username}</div>
            <div class="sr-user">@${u.username}</div>
          </div>
        </a>`).join('');
      searchResults.style.display = 'block';
    } catch(e) { 
      searchResults.style.display = 'none'; 
    }
  }, 300);
});

document.addEventListener('click', e => {
  if (!e.target.closest('.nav-search')) searchResults.style.display = 'none';
});

// Mark active nav link
(function(){
  const path = window.location.pathname;
  document.querySelectorAll('.bottom-nav a, .nav-links a').forEach(a => {
    try {
      const href = new URL(a.href).pathname;
      if (href === path) a.classList.add('active');
    } catch(e){}
  });
})();

// Theme toggle
(function(){
  const saved = localStorage.getItem('theme') || document.documentElement.getAttribute('data-theme') || 'dark';

  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    document.cookie = `theme=${t};path=/;max-age=31536000`;
    document.querySelectorAll('.theme-btn')
    .forEach(b => b.classList.toggle('active', b.dataset.theme === t));
  }

  function buildToggle() {
    if (document.querySelector('.theme-toggle')) return;
    const wrap = document.createElement('div');
    wrap.className = 'theme-toggle';
    [['light','☀️'],['dark','🌙']].forEach(([t,icon]) => {
      const btn = document.createElement('button');
      btn.className = 'theme-btn' + (t === saved ? ' active' : '');
      btn.dataset.theme = t;
      btn.textContent = icon;
      btn.title = t === 'light' ? 'Light mode' : 'Dark mode';
      btn.onclick = () => applyTheme(t);
      wrap.appendChild(btn);
    });
    document.body.appendChild(wrap);
  }

  applyTheme(saved);
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', buildToggle);
  } else {
    buildToggle();
  }
})();
</script>
<?php endif; ?>

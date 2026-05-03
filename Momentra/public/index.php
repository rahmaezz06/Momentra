<?php
// ============================================================
//  Bootstrap
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';

startSession();

// ============================================================
//  Router
// ============================================================
$router = new Router();

// ── Auth ────────────────────────────────────────────────────
$router->get('/login',               'AuthController', 'loginForm');
$router->post('/login',              'AuthController', 'loginPost');
$router->get('/register',            'AuthController', 'registerForm');
$router->post('/register',           'AuthController', 'registerPost');
$router->get('/logout',              'AuthController', 'logout');
$router->get('/forgot-password',     'AuthController', 'forgotForm');
$router->post('/forgot-password',    'AuthController', 'forgotPost');
$router->get('/reset-password',      'AuthController', 'resetForm');
$router->post('/reset-password',     'AuthController', 'resetPost');
$router->get('/change-password',     'AuthController', 'changeForm');
$router->post('/change-password',    'AuthController', 'changePost');

// ── Feed ────────────────────────────────────────────────────
$router->get('/',                    'FeedController', 'index');

// ── Posts ───────────────────────────────────────────────────
$router->get('/post',                'PostController', 'show');
$router->get('/post/create',         'PostController', 'create');
$router->post('/post/create',        'PostController', 'create');
$router->get('/post/edit',           'PostController', 'edit');
$router->post('/post/edit',          'PostController', 'edit');

// ── Profile ─────────────────────────────────────────────────
$router->get('/profile',             'ProfileController', 'show');
$router->get('/profile/edit',        'ProfileController', 'edit');
$router->post('/profile/edit',       'ProfileController', 'edit');
$router->get('/profile/follows',     'ProfileController', 'followList');
$router->get('/profile/saved',       'ProfileController', 'saved');
$router->post('/profile/delete',     'ProfileController', 'deleteAccount');

// ── API ─────────────────────────────────────────────────────
$router->post('/api/like',           'ApiController', 'like');
$router->post('/api/save',           'ApiController', 'save');
$router->post('/api/comment',        'ApiController', 'comment');
$router->post('/api/delete-comment', 'ApiController', 'deleteComment');
$router->post('/api/like-comment',   'ApiController', 'likeComment');
$router->get('/api/replies',         'ApiController', 'getReplies');
$router->post('/api/follow',         'ApiController', 'follow');
$router->post('/api/delete-post',    'ApiController', 'deletePost');
$router->get('/api/likes',           'ApiController', 'getLikes');
$router->get('/api/search',          'ApiController', 'search');
$router->get('/api/csrf',            'ApiController', 'csrfToken');

// ── Dispatch ────────────────────────────────────────────────
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /Momentra/public/index.php
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // شيل query string

// شيل اسم الـ script من الأول عشان نوصل للـ path بس
if (str_starts_with($requestUri, $scriptName)) {
    $uri = substr($requestUri, strlen($scriptName));
} else {
    $uri = $requestUri;
}

$uri = '/' . trim($uri, '/') ?: '/';
$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);

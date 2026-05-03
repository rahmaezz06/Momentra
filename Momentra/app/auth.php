<?php

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 86400 * 30,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('momentra_session');
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $user = db_fetch_one('SELECT * FROM users WHERE id = ?', 'i', [$_SESSION['user_id']]);
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            setcookie('remember_user', '', time() - 3600, '/');
            setcookie('momentra_session', '', time() - 3600, '/');
        }
    }
    return $user ?: null;
}

function login(string $email, string $password): bool {
    $user = db_fetch_one('SELECT * FROM users WHERE email = ?', 's', [strtolower(trim($email))]);
    if ($user && password_verify($password, $user['password_hash'])) {
        startSession();
        $_SESSION = [];
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        session_regenerate_id(false);
        $token = hash_hmac('sha256', $user['id'] . ':' . $user['password_hash'], APP_NAME);
        setcookie('remember_user', $user['id'] . ':' . $token, time() + 86400 * 30, '/', '', false, true);
        return true;
    }
    return false;
}

function logout(): void {
    startSession();
    $_SESSION = [];
    session_destroy();
    setcookie('remember_user', '', time() - 3600, '/');
}

function register(string $username, string $email, string $password, string $fullName): array {
    $username = trim($username);
    $email    = strtolower(trim($email));
    $errors   = [];

    if (empty($username))                        $errors[] = 'Username is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 6)                   $errors[] = 'Password must be at least 6 characters.';

    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    $existing = db_fetch_one('SELECT id FROM users WHERE username = ? OR email = ?', 'ss', [$username, $email]);
    if ($existing) return ['success' => false, 'errors' => ['Username or email already taken.']];

    $hash = password_hash($password, PASSWORD_BCRYPT);
    db_query('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)',
        'ssss', [$username, $email, $hash, $fullName]);

    return ['success' => true, 'user_id' => db_last_id()];
}

function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    startSession();
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'csrf', 'message' => 'Session expired, please refresh the page.']));
    }
}

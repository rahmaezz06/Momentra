<?php
// ============================================================
//  Authentication & Session Helpers
// ============================================================

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                   ($_SERVER['SERVER_PORT'] ?? 80) == 443;
        // Use specific path for XAMPP subfolder installations
        $cookiePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';
        session_set_cookie_params([
            'lifetime' => 86400 * 30,
            'path'     => $cookiePath,
            'secure'   => $isHttps,
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

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user ?: null;
}

function login(string $email, string $password): bool {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        startSession();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        // Remember me cookie - use HMAC token instead of plain base64
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
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

function register(string $username, string $email, string $password, string $fullName): array {
    $username = trim($username);
    $email    = strtolower(trim($email));
    $errors   = [];

    if (empty($username))
        $errors[] = 'Username is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email address.';
    if (strlen($password) < 6)
        $errors[] = 'Password must be at least 6 characters.';

    if (!empty($errors)) 
        return ['success' => false, 'errors' => $errors];

    // Check uniqueness
    $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Username or email already taken.']];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $email, $hash, $fullName]);
    return ['success' => true, 'user_id' => db()->lastInsertId()];
}

// ============================================================
//  CSRF Protection
// ============================================================
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
        die('CSRF token mismatch');
    }
}

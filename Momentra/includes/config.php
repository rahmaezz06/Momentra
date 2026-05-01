<?php
// ============================================================
//  Output Buffering — must be first to allow redirects anywhere
// ============================================================
if (!ob_get_level()) ob_start();

// ============================================================
//  Database Configuration
//  Edit these values to match your MySQL setup
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'instagram_clone');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App settings
define('APP_NAME', 'Momentra');

// Auto-detect BASE_URL so it works on any localhost setup (XAMPP, WAMP, etc.)
if (!defined('BASE_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // __DIR__ is .../Momentra/includes, go up one level to get the app root inside htdocs/www
    $appRoot  = dirname(__DIR__);
    $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    if ($docRoot && str_starts_with($appRoot, $docRoot)) {
        $path = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
    } else {
        $path = '/Momentra'; // fallback
    }
    define('BASE_URL', $scheme . '://' . $host . $path);
}
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ============================================================
//  PDO Database Connection (Singleton)
// ============================================================
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

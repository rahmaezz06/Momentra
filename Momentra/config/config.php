<?php
// ============================================================
//  Output Buffering
// ============================================================
if (!ob_get_level()) ob_start();

// ============================================================
//  Paths & URLs
// ============================================================
define('BASE_PATH', dirname(__DIR__));

if (!defined('BASE_URL')) {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appRoot = dirname(__DIR__);
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    if ($docRoot && str_starts_with($appRoot, $docRoot)) {
        $path = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
    } else {
        $path = '/Momentra';
    }
    define('BASE_URL', $scheme . '://' . $host . $path . '/public/index.php');
}

// ============================================================
//  Database Configuration
// ============================================================
define('DB_HOST',    'localhost');
define('DB_NAME',    'instagram_clone');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
//  App Settings
// ============================================================
define('APP_NAME',      'Momentra');
define('UPLOAD_PATH',   BASE_PATH . '/public/uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ============================================================
//  Database Connection (Singleton)
// ============================================================
function db(): mysqli {
    static $mysqli = null;
    if ($mysqli === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $mysqli->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $mysqli;
}

// ============================================================
//  DB Helpers
// ============================================================
function db_query(string $sql, string $types = '', array $params = []): mysqli_stmt {
    $stmt = db()->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function db_fetch_all(string $sql, string $types = '', array $params = []): array {
    $stmt = db_query($sql, $types, $params);
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function db_fetch_one(string $sql, string $types = '', array $params = []): ?array {
    $stmt = db_query($sql, $types, $params);
    $row  = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function db_fetch_scalar(string $sql, string $types = '', array $params = []) {
    $stmt = db_query($sql, $types, $params);
    $row  = $stmt->get_result()->fetch_row();
    return $row ? $row[0] : null;
}

function db_last_id(): int {
    return (int) db()->insert_id;
}

// ============================================================
//  Load Core Classes & Helpers
// ============================================================
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/app/helpers.php';
require_once BASE_PATH . '/app/auth.php';

// ============================================================
//  Autoload Models & Controllers
// ============================================================
spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/app/Models/'      . $class . '.php',
        BASE_PATH . '/app/Controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

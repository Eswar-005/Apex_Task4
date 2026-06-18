<?php
// secure-app/config/database.php - Hardened Database Connection and Configuration

// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SECURE_DB_HOST', '127.0.0.1');
define('SECURE_DB_PORT', '3307');
define('SECURE_DB_NAME', 'blog_secure');
define('SECURE_DB_USER', 'root');
define('SECURE_DB_PASS', '');

try {
    $dsn = "mysql:host=" . SECURE_DB_HOST . ";port=" . SECURE_DB_PORT . ";dbname=" . SECURE_DB_NAME . ";charset=utf8mb4";
    
    // Create connection with security safeguards
    $pdo = new PDO($dsn, SECURE_DB_USER, SECURE_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Hard security: disable emulation to prevent injection issues
    ]);
} catch (PDOException $e) {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center; background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; margin: 50px auto; max-width: 500px; border-radius: 8px;'>
            <h2 style='margin-top: 0;'>Database Connection Error</h2>
            <p>Could not connect to the secure database on port " . SECURE_DB_PORT . ".</p>
            <small style='color: #9b2c2c;'>" . htmlspecialchars($e->getMessage()) . "</small>
         </div>");
}

/**
 * Returns the global PDO connection instance
 */
function getDbConnection() {
    global $pdo;
    return $pdo;
}


/* ==========================================
   CSRF PROTECTION HELPERS
   ========================================== */

function generate_secure_csrf_token() {
    if (empty($_SESSION['secure_csrf_token'])) {
        $_SESSION['secure_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['secure_csrf_token'];
}

function verify_secure_csrf_token($token) {
    if (empty($_SESSION['secure_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['secure_csrf_token'], $token);
}

function secure_csrf_field() {
    $token = generate_secure_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/* ==========================================
   FLASH MESSAGES HELPERS
   ========================================== */

function set_secure_flash_message($type, $message) {
    $_SESSION['secure_flash'][$type] = $message;
}

function get_secure_flash_message($type) {
    if (!isset($_SESSION['secure_flash'][$type])) {
        return '';
    }
    $message = $_SESSION['secure_flash'][$type];
    unset($_SESSION['secure_flash'][$type]);
    return $message;
}

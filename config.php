<?php
// config.php - Central Database connection, Session management, and Helper utilities

// Set secure session cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start the session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'blog');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Construct DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Create a PDO connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center; background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; margin: 50px auto; max-width: 500px; border-radius: 8px;'>
            <h2 style='margin-top: 0;'>Database Connection Error</h2>
            <p>Could not connect to the database. Make sure MySQL is running on port " . DB_PORT . ".</p>
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
   SECURITY HELPERS (CSRF Protection)
   ========================================== */

/**
 * Generate a cryptographically secure CSRF token and save it to the session
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the stored session token
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render a hidden input field with the CSRF token
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/* ==========================================
   FLASH MESSAGES HELPERS
   ========================================== */

/**
 * Set a temporary flash message in session
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Check if a specific flash message type exists
 */
function has_flash_message($type) {
    return isset($_SESSION['flash'][$type]);
}

/**
 * Retrieve and consume (clear) a flash message
 */
function get_flash_message($type) {
    if (!isset($_SESSION['flash'][$type])) {
        return '';
    }
    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $message;
}

/* ==========================================
   DATETIME FORMATTING HELPERS
   ========================================== */

/**
 * Format a datetime string to relative time ago (e.g. "5 mins ago", "2 hours ago")
 */
function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 1) {
        return 'just now';
    }
    
    $intervals = [
        31536000 => 'year',
        2592000  => 'month',
        604800   => 'week',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute',
        1        => 'second'
    ];
    
    foreach ($intervals as $secs => $label) {
        $div = $diff / $secs;
        if ($div >= 1) {
            $value = round($div);
            return $value . ' ' . $label . ($value > 1 ? 's' : '') . ' ago';
        }
    }
    
    return date('M d, Y', $time);
}

/**
 * Safely highlights matching search query text in HTML-escaped content
 */
function highlight_search($text, $search) {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    if ($search === '') {
        return $escaped;
    }
    
    $escaped_search = preg_quote($search, '/');
    // Using preg_replace with case-insensitive modifier
    return preg_replace('/(' . $escaped_search . ')/i', '<mark class="search-highlight">$1</mark>', $escaped);
}

/**
 * Calculates reading time in minutes based on average reading speed
 */
function calculate_read_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $words_per_minute = 200;
    $minutes = ceil($word_count / $words_per_minute);
    return (int)max(1, $minutes);
}


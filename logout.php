<?php
require_once 'config.php';

// Securely log out the user by clearing session data
$_SESSION = [];

// Destroy session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy active session
session_destroy();

// Start a fresh, unauthenticated session to pass a logout success flash message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash_message('success', 'You have been successfully logged out.');

header("Location: login.php");
exit;

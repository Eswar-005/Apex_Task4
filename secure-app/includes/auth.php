<?php
// secure-app/includes/auth.php - Authentication and Authorization Gatekeeper

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Checks if the current user is authenticated
 */
function is_secure_authenticated() {
    return isset($_SESSION['secure_user_id']);
}

/**
 * Enforces role restrictions. Redirects to dashboard if permission is denied.
 * @param array|string $allowedRoles Single role string or array of allowed roles
 */
function require_secure_role($allowedRoles) {
    if (!is_secure_authenticated()) {
        set_secure_flash_message('error', 'You must be logged in to view that page.');
        header('Location: login.php');
        exit;
    }

    $userRole = $_SESSION['secure_role'] ?? 'user';
    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    if (!in_array($userRole, $roles)) {
        set_secure_flash_message('error', 'Access Denied: You do not have permission to access that area.');
        header('Location: dashboard.php');
        exit;
    }
}

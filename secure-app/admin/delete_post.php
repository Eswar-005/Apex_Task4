<?php
// secure-app/admin/delete_post.php - Strict Backend Role Access Controller for Post Deletion
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Enforce backend authentication and administrator role gate check
require_secure_role('admin');

// Enforce POST requests strictly
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_secure_flash_message('error', 'Method not allowed. Destructive endpoints require a secure POST request.');
    header('Location: ../dashboard.php');
    exit;
}

// CSRF validation
$token = $_POST['csrf_token'] ?? '';
if (!verify_secure_csrf_token($token)) {
    set_secure_flash_message('error', 'Security check verification failed. Please try again.');
    header('Location: ../dashboard.php');
    exit;
}

$post_id = $_POST['id'] ?? null;

if ($post_id) {
    try {
        // Parametric bound query to secure deletion
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        set_secure_flash_message('success', 'Post deleted successfully.');
    } catch (PDOException $e) {
        set_secure_flash_message('error', 'Failed to delete the post due to a database exception.');
    }
} else {
    set_secure_flash_message('error', 'Post deletion failed: no post ID was supplied.');
}

header('Location: ../dashboard.php');
exit;

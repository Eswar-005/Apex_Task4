<?php
require_once 'config.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'You must be logged in to delete a post.');
    header('Location: login.php');
    exit;
}

// Strictly enforce POST request method for destructive actions (pro-level security)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('error', 'Invalid action. Post deletion must be sent via a secure form POST request.');
    header('Location: index.php');
    exit;
}

// CSRF Verification
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    set_flash_message('error', 'Security check failed. Please refresh and try again.');
    header('Location: index.php');
    exit;
}

$post_id = $_POST['id'] ?? null;

if ($post_id) {
    try {
        // Delete post using prepared statement
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        set_flash_message('success', 'Post deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Database error: failed to delete post.');
    }
} else {
    set_flash_message('error', 'Post deletion failed: no post ID specified.');
}

// Redirect back to home dashboard
header('Location: index.php');
exit;

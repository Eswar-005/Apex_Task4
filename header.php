<?php
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

// Generate initials for profile avatar
$avatar_initial = '';
if ($is_logged_in && !empty($username)) {
    $avatar_initial = strtoupper(substr($username, 0, 1));
}

// Default page title
$title = $page_title ?? 'DevBlog - Share Your Thoughts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="brand">
                <span class="brand-icon">📝</span> DevBlog
            </a>
            <ul class="nav-links">
                <?php if ($is_logged_in): ?>
                    <li class="nav-user">
                        <div class="user-avatar" title="<?php echo htmlspecialchars($username); ?>">
                            <?php echo htmlspecialchars($avatar_initial); ?>
                        </div>
                        <span class="user-name">Hello, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                    </li>
                    <li><a href="create.php" class="btn btn-primary btn-sm">Create Post</a></li>
                    <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link-item">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="container">

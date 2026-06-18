<?php
// secure-app/dashboard.php - Secure Dashboard with UI-level Access Controls (RBAC)
require_once 'config/database.php';
require_once 'includes/auth.php';

$success = get_secure_flash_message('success');
$error = get_secure_flash_message('error');

$is_logged_in = is_secure_authenticated();
$user_role = $_SESSION['secure_role'] ?? 'user';
$username = $_SESSION['secure_username'] ?? '';

// Check roles for rendering
$is_admin = ($user_role === 'admin');
$is_editor = ($user_role === 'editor');
$can_write = ($is_admin || $is_editor);

// Fetch posts
$posts = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: Could not fetch posts.';
}

$avatar_initial = '';
if ($is_logged_in && !empty($username)) {
    $avatar_initial = strtoupper(substr($username, 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Application Demo</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="brand">📝 DevBlog <span style="font-size: 0.8rem; background: #818cf8; color: #fff; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">SECURE</span></a>
            <ul class="nav-links">
                <?php if ($is_logged_in): ?>
                    <li class="nav-user">
                        <div class="user-avatar" title="<?php echo htmlspecialchars($username); ?> (Role: <?php echo htmlspecialchars($user_role); ?>)">
                            <?php echo htmlspecialchars($avatar_initial); ?>
                        </div>
                        <span class="user-name">Hello, <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo htmlspecialchars($user_role); ?>)</span>
                    </li>
                    <?php if ($can_write): ?>
                        <li><span class="btn btn-primary btn-sm" onclick="alert('Creating new posts is stubbed out for secure-app layout structure demo.')">+ New Post</span></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link-item" id="login-nav-btn">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="container">
        <!-- Alert Feedbacks -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-top: 1rem;">
                <span>✅</span> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-top: 1rem;">
                <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Stories & Ideas</h1>
                <p>Fully secure space utilizing prepared statements, stateful CSRF mitigation, and strict RBAC layers.</p>
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No posts found</h3>
                <p>Register or log in with admin/editor permissions to seed or add new posts.</p>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div>
                            <div class="post-meta">
                                <span>📅</span> 
                                <?php echo htmlspecialchars($post['created_at']); ?>
                            </div>
                            <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <div class="post-content"><?php echo htmlspecialchars($post['content']); ?></div>
                        </div>
                        
                        <?php if ($is_logged_in && ($is_admin || $is_editor)): ?>
                            <div class="post-actions">
                                <span class="btn btn-secondary btn-sm" onclick="alert('Editing posts is a stub demo.')">Edit</span>
                                
                                <?php if ($is_admin): ?>
                                <!-- Secure POST-based Deletion Form with CSRF Protection -->
                                <form action="admin/delete_post.php" method="POST" class="inline-form" 
                                      onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <?php echo secure_csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <!-- Glassmorphic Login Modal Prompt Overlay -->
    <div class="modal-overlay" id="login-modal-overlay">
        <div class="modal-card">
            <button class="modal-close-btn" id="modal-close-btn">&times;</button>
            <h2>Welcome Back</h2>
            <p class="subtitle">Enter your credentials to manage your posts</p>

            <form method="POST" action="login.php" novalidate id="modal-login-form">
                <?= secure_csrf_field() ?>

                <label for="modal-username">Username</label>
                <input type="text" id="modal-username" name="username" placeholder="Enter Username" required>

                <label for="modal-password">Password</label>
                <input type="password" id="modal-password" name="password" placeholder="Enter Password" required>

                <button type="submit" class="submit-btn">Log In</button>
            </form>

            <p class="footer" style="margin-top: 22px; text-align: center; font-size: 0.9rem; color: #8b90ad;">
                Don't have an account yet? <a href="register.php" style="color: #9b8cf9; font-weight: 600; text-decoration: none;">Register</a>
            </p>
        </div>
    </div>

    <style>
    /* Custom Modal CSS Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 10, 22, 0.75);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.active {
        display: flex;
        opacity: 1;
    }
    .modal-card {
        width: 100%;
        max-width: 420px;
        background: #15172c;
        border: 1px solid rgba(139, 92, 246, 0.28);
        border-radius: 24px;
        padding: 40px 36px;
        box-shadow: 0 0 0 1px rgba(139, 92, 246, 0.06), 0 30px 60px -20px rgba(91, 60, 220, 0.35);
        position: relative;
        transform: scale(0.95);
        transition: transform 0.3s ease;
        text-align: left;
    }
    .modal-overlay.active .modal-card {
        transform: scale(1);
    }
    .modal-close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: #8b90ad;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
        padding: 5px;
        transition: color 0.2s ease;
    }
    .modal-close-btn:hover {
        color: #f5f3ff;
    }
    .modal-card h2 {
        font-family: 'Baloo 2', 'Inter', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 8px;
        text-align: center;
        color: #f5f3ff;
        letter-spacing: -0.02em;
    }
    .modal-card .subtitle {
        color: #8b90ad;
        text-align: center;
        margin: 0 0 24px;
        font-size: 0.95rem;
    }
    .modal-card label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: #c7cae3;
        margin: 18px 0 8px;
    }
    .modal-card input {
        width: 100%;
        padding: 13px 16px;
        border-radius: 12px;
        border: 1px solid rgba(139, 92, 246, 0.15);
        background: #0e0f20;
        color: #f5f3ff;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.2s ease;
    }
    .modal-card input::placeholder {
        color: #5d6080;
    }
    .modal-card input:focus {
        outline: none;
        border-color: #5b5ff0;
        box-shadow: 0 0 0 3px rgba(91, 95, 240, 0.2);
    }
    .modal-card input:invalid:not(:placeholder-shown) {
        border-color: #f87171;
        box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.2);
    }
    .modal-card button.submit-btn {
        width: 100%;
        margin-top: 26px;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #5b5ff0, #9d4ff0);
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .modal-card button.submit-btn:hover {
        filter: brightness(1.08);
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginNavBtn = document.getElementById('login-nav-btn');
        const modalOverlay = document.getElementById('login-modal-overlay');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        const modalLoginForm = document.getElementById('modal-login-form');
        const modalUsername = document.getElementById('modal-username');
        const modalPassword = document.getElementById('modal-password');

        if (loginNavBtn && modalOverlay) {
            loginNavBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modalOverlay.classList.add('active');
                setTimeout(() => { modalUsername.focus(); }, 50);
            });

            modalCloseBtn.addEventListener('click', function() {
                modalOverlay.classList.remove('active');
            });

            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) {
                    modalOverlay.classList.remove('active');
                }
            });

            // Client-side validation feedback
            modalLoginForm.addEventListener('submit', function(e) {
                let valid = true;

                if (modalUsername.value.trim() === '') {
                    modalUsername.style.borderColor = '#f87171';
                    valid = false;
                } else {
                    modalUsername.style.borderColor = 'rgba(139, 92, 246, 0.15)';
                }

                if (modalPassword.value === '') {
                    modalPassword.style.borderColor = '#f87171';
                    valid = false;
                } else {
                    modalPassword.style.borderColor = 'rgba(139, 92, 246, 0.15)';
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</body>
</html>

<?php
require_once 'config.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'You must be logged in to create a post.');
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title) || empty($content)) {
            $error = 'Both Title and Content are required.';
        } elseif (strlen($title) < 3) {
            $error = 'Title must be at least 3 characters long.';
        } elseif (strlen($content) < 5) {
            $error = 'Content must be at least 5 characters long.';
        } else {
            try {
                // Insert post into the database
                $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
                if ($stmt->execute([$title, $content])) {
                    set_flash_message('success', 'Post published successfully!');
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Failed to publish post. Please try again.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Write New Post - DevBlog';
require_once 'header.php';
?>

<div class="page-header">
    <a href="index.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<div class="glass-panel" style="max-width: 800px; margin: 0 auto;">
    <h1 class="page-title" style="margin-bottom: 2rem;">Write New Post</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="create.php" method="POST">
        <!-- CSRF Token hidden field -->
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="title" class="form-label">Post Title</label>
            <input type="text" id="title" name="title" class="form-control" 
                   placeholder="e.g., Mastering PHP Prepared Statements" required 
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                   maxlength="255">
        </div>

        <div class="form-group">
            <label for="content" class="form-label">Post Content</label>
            <textarea id="content" name="content" class="form-control" 
                      placeholder="Start typing your article content here..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Publish Post</button>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>

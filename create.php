<?php
require_once 'config.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'You must be logged in to create a post.');
    header('Location: login.php');
    exit;
}

// Restrict access to admin or editor
if (($_SESSION['role'] ?? 'user') === 'user') {
    set_flash_message('error', 'You do not have permission to create posts.');
    header('Location: index.php');
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
                   placeholder="Enter Post Title" required 
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                   minlength="3" maxlength="255">
            <div class="character-counter" id="title-counter" style="margin-top: 0.25rem; font-size: 0.8rem; text-align: right; color: var(--text-muted);">
                0 / 255 characters (minimum 3)
            </div>
        </div>

        <div class="form-group">
            <label for="content" class="form-label">Post Content</label>
            <textarea id="content" name="content" class="form-control" 
                      placeholder="Enter Post Content" required minlength="5"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
            <div class="character-counter" id="content-counter" style="margin-top: 0.25rem; font-size: 0.8rem; text-align: right; color: var(--text-muted);">
                0 characters (minimum 5)
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Publish Post</button>
        </div>
    </form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const titleCounter = document.getElementById('title-counter');
    const contentCounter = document.getElementById('content-counter');
    const form = document.querySelector('form');
    
    // Add touched class on input / blur
    const addTouched = (e) => {
        e.target.classList.add('touched');
    };
    
    titleInput.addEventListener('blur', addTouched);
    titleInput.addEventListener('input', addTouched);
    contentInput.addEventListener('blur', addTouched);
    contentInput.addEventListener('input', addTouched);

    const updateCounters = () => {
        const titleLen = titleInput.value.length;
        const contentLen = contentInput.value.length;
        
        titleCounter.textContent = `${titleLen} / 255 characters (minimum 3)`;
        contentCounter.textContent = `${contentLen} characters (minimum 5)`;
        
        if (titleLen >= 3 && titleLen <= 255) {
            titleCounter.style.color = 'var(--text-muted)';
        } else if (titleInput.classList.contains('touched')) {
            titleCounter.style.color = '#f43f5e';
        }
        
        if (contentLen >= 5) {
            contentCounter.style.color = 'var(--text-muted)';
        } else if (contentInput.classList.contains('touched')) {
            contentCounter.style.color = '#f43f5e';
        }
    };
    
    titleInput.addEventListener('input', updateCounters);
    contentInput.addEventListener('input', updateCounters);
    
    // Run initial counts
    updateCounters();
    
    form.addEventListener('submit', function() {
        titleInput.classList.add('touched');
        contentInput.classList.add('touched');
        updateCounters();
    });
});
</script>
</div>

<?php require_once 'footer.php'; ?>

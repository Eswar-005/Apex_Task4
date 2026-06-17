<?php
require_once 'config.php';

// Retrieve any flash messages before rendering header
$success = get_flash_message('success');
$error = get_flash_message('error');

// Fetch posts
try {
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to fetch posts: ' . $e->getMessage();
    $posts = [];
}

$page_title = 'DevBlog - Share Your Thoughts';
require_once 'header.php';
?>

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
        <p>A secure CRUD space to write, read, edit, and delete thoughts.</p>
    </div>
    <?php if ($is_logged_in): ?>
        <a href="create.php" class="btn btn-primary">+ New Post</a>
    <?php endif; ?>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <h3>No posts yet</h3>
        <p>Be the first to share your perspective with the world.</p>
        <?php if ($is_logged_in): ?>
            <a href="create.php" class="btn btn-primary">Create First Post</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">Log in to Post</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="posts-grid">
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <div>
                    <div class="post-meta" title="<?php echo htmlspecialchars($post['created_at']); ?>">
                        <span>📅</span> 
                        <?php echo htmlspecialchars(time_ago($post['created_at'])); ?>
                    </div>
                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="post-content"><?php echo htmlspecialchars($post['content']); ?></div>
                </div>
                
                <?php if ($is_logged_in): ?>
                    <div class="post-actions">
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                        
                        <!-- Secure POST-based Deletion Form -->
                        <form action="delete.php" method="POST" class="inline-form" 
                              onsubmit="return confirm('Are you sure you want to delete this post?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

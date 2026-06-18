<?php
require_once 'config.php';

// Retrieve any flash messages before rendering header
$success = get_flash_message('success');
$error = get_flash_message('error');

// Pagination & Search parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$limit = 6;
$offset = ($page - 1) * $limit;

// Fetch posts & pagination data
try {
    if (!empty($search)) {
        // Count total matching posts
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :query1 OR content LIKE :query2");
        $count_stmt->execute(['query1' => '%' . $search . '%', 'query2' => '%' . $search . '%']);
        $total_posts = $count_stmt->fetchColumn();
        
        $total_pages = ceil($total_posts / $limit);
        if ($total_pages < 1) {
            $total_pages = 1;
        }
        if ($page > $total_pages) {
            $page = $total_pages;
            $offset = ($page - 1) * $limit;
        }
        
        // Fetch matching posts
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE :query1 OR content LIKE :query2 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':query1', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':query2', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } else {
        // Count total posts using a prepared statement for consistency
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts");
        $count_stmt->execute();
        $total_posts = $count_stmt->fetchColumn();
        
        $total_pages = ceil($total_posts / $limit);
        if ($total_pages < 1) {
            $total_pages = 1;
        }
        if ($page > $total_pages) {
            $page = $total_pages;
            $offset = ($page - 1) * $limit;
        }
        
        // Fetch posts
        $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = 'Failed to fetch posts: ' . $e->getMessage();
    $posts = [];
    $total_posts = 0;
    $total_pages = 1;
}

// Calculate pagination stats range for display
$start_index = $total_posts > 0 ? $offset + 1 : 0;
$end_index = min($offset + $limit, $total_posts);

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
    <?php if ($is_logged_in && ($is_admin || $is_editor)): ?>
        <a href="create.php" class="btn btn-primary">+ New Post</a>
    <?php endif; ?>
</div>

<!-- Search Form Container -->
<div class="search-container glass-panel">
    <form action="index.php" method="GET" class="search-form">
        <div class="search-input-group">
            <span class="search-icon">🔍</span>
            <input type="text" name="q" class="form-control search-input" placeholder="Search posts by title or content..." value="<?php echo htmlspecialchars($search); ?>">
            <?php if (!empty($search)): ?>
                <a href="index.php" class="clear-search-btn" title="Clear search">&times;</a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary search-submit-btn">Search</button>
    </form>
</div>

<?php if (!empty($search)): ?>
    <div class="search-results-indicator">
        <p>Showing search results for: <strong>"<?php echo htmlspecialchars($search); ?>"</strong> (<?php echo $total_posts; ?> match<?php echo $total_posts === 1 ? '' : 'es'; ?> found)</p>
        <a href="index.php" class="btn btn-secondary btn-sm">Clear Filter</a>
    </div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <h3>No posts found</h3>
        <?php if (!empty($search)): ?>
            <p>We couldn't find any posts matching "<?php echo htmlspecialchars($search); ?>". Try searching for different keywords.</p>
            <a href="index.php" class="btn btn-primary">Clear Search & View All</a>
        <?php else: ?>
            <p>Be the first to share your perspective with the world.</p>
            <?php if ($is_logged_in): ?>
                <a href="create.php" class="btn btn-primary">Create First Post</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Log in to Post</a>
            <?php endif; ?>
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
                        <span class="meta-dot">&bull;</span>
                        <span>⏱️</span> <?php echo calculate_read_time($post['content']); ?> min read
                    </div>
                    <h2 class="post-title"><?php echo highlight_search($post['title'], $search); ?></h2>
                    <div class="post-content"><?php echo highlight_search($post['content'], $search); ?></div>
                </div>
                
                <?php if ($is_logged_in && ($is_admin || $is_editor)): ?>
                    <div class="post-actions">
                        <a href="edit.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                        
                        <?php if ($is_admin): ?>
                        <!-- Secure POST-based Deletion Form -->
                        <form action="delete.php" method="POST" class="inline-form" 
                              onsubmit="return confirm('Are you sure you want to delete this post?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination Statistics -->
    <div class="pagination-info">
        Showing <span><?php echo $start_index; ?></span>&ndash;<span><?php echo $end_index; ?></span> of <span><?php echo $total_posts; ?></span> post<?php echo $total_posts === 1 ? '' : 's'; ?>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <!-- Previous Button -->
            <?php if ($page > 1): ?>
                <a href="index.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="pagination-btn pagination-prev">&larr; Prev</a>
            <?php else: ?>
                <span class="pagination-btn pagination-prev disabled">&larr; Prev</span>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1) {
                echo '<a href="index.php?page=1' . (!empty($search) ? '&q=' . urlencode($search) : '') . '" class="pagination-btn">1</a>';
                if ($start_page > 2) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
            }

            for ($i = $start_page; $i <= $end_page; $i++):
                if ($i == $page): ?>
                    <span class="pagination-btn active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="index.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="pagination-btn"><?php echo $i; ?></a>
                <?php endif;
            endfor;

            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                echo '<a href="index.php?page=' . $total_pages . (!empty($search) ? '&q=' . urlencode($search) : '') . '" class="pagination-btn">' . $total_pages . '</a>';
            }
            ?>

            <!-- Next Button -->
            <?php if ($page < $total_pages): ?>
                <a href="index.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="pagination-btn pagination-next">Next &rarr;</a>
            <?php else: ?>
                <span class="pagination-btn pagination-next disabled">Next &rarr;</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

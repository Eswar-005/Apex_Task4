<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
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
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Password complexity rules: minimum 6 characters, at least one letter and one number
        $has_letter = preg_match('/[a-zA-Z]/', $password);
        $has_number = preg_match('/[0-9]/', $password);

        if (empty($username) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Username can only contain letters, numbers, and underscores.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif (!$has_letter || !$has_number) {
            $error = 'Password must contain at least one letter and one number.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username is already taken.';
                } else {
                    // Hash securely with bcrypt
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Insert user
                    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    if ($stmt->execute([$username, $hashed_password])) {
                        set_flash_message('success', 'Registration successful! Please log in.');
                        header('Location: login.php');
                        exit;
                    } else {
                        $error = 'Something went wrong. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Create Account - DevBlog';
require_once 'header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join DevBlog today to publish your stories</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <!-- CSRF Token Hidden Input -->
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="Choose a unique username"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       required autocomplete="off" minlength="3" maxlength="50">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter a secure password (e.g. Pass123)" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Register
            </button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

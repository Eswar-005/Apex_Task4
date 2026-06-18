<?php
/**
 * secure-app/login.php
 *
 * The classic SQLi target. The commented-out "vulnerable" version below
 * is left in ONLY as a reference of what NOT to do - it never executes.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_secure_authenticated()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success_msg = get_secure_flash_message('success');
if (isset($_GET['registered'])) {
    $success_msg = 'Account created — you can log in now.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_secure_csrf_token($token)) {
        $errors[] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errors[] = 'Username and password are required.';
        } else {
            $pdo = getDbConnection();

            /*
             * VULNERABLE (do not use):
             *   $sql = "SELECT * FROM users WHERE username = '$username'";
             * Submitting   admin_jane' OR '1'='1
             * as the username would turn the WHERE clause into a tautology
             * and return every row, bypassing the password check entirely.
             */

            // SECURE: the placeholder is bound as data, never concatenated
            // into the SQL string, so injected quotes/operators are inert.
            // Note: DB table column is `password`, so we alias it to `password_hash`
            $stmt = $pdo->prepare('SELECT id, username, password AS password_hash, role FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true); // mitigates session fixation
                $_SESSION['secure_user_id']   = $user['id'];
                $_SESSION['secure_username']  = $user['username'];
                $_SESSION['secure_role']      = $user['role'];
                header('Location: dashboard.php');
                exit;
            }

            // Deliberately generic - don't reveal whether the username or
            // password was wrong (avoids user enumeration).
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in - Secure Application Demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #0a0a16;
            --card-bg: #15172c;
            --input-bg: #0e0f20;
            --border-glow: rgba(139, 92, 246, 0.28);
            --text-heading: #f5f3ff;
            --text-subtitle: #8b90ad;
            --text-label: #c7cae3;
            --placeholder: #5d6080;
            --accent-from: #5b5ff0;
            --accent-to: #9d4ff0;
            --link: #9b8cf9;
            --error: #f87171;
            --success: #4ade80;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(circle at 30% 20%, #1b1740 0%, var(--bg-deep) 55%);
            color: var(--text-heading);
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            border: 1px solid var(--border-glow);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 0 0 1px rgba(139, 92, 246, 0.06), 0 30px 60px -20px rgba(91, 60, 220, 0.35);
        }
        h1 {
            font-family: 'Baloo 2', 'Inter', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px;
            text-align: center;
        }
        .subtitle {
            color: var(--text-subtitle);
            text-align: center;
            margin: 0 0 28px;
            font-size: 0.95rem;
        }
        label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-label);
            margin: 18px 0 8px;
        }
        input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 12px;
            border: 1px solid rgba(139, 92, 246, 0.15);
            background: var(--input-bg);
            color: var(--text-heading);
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        input::placeholder { color: var(--placeholder); }
        input:focus {
            outline: none;
            border-color: var(--accent-from);
            box-shadow: 0 0 0 3px rgba(91, 95, 240, 0.2);
        }
        input:invalid:not(:placeholder-shown) {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.2);
        }
        button {
            width: 100%;
            margin-top: 26px;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent-from), var(--accent-to));
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        button:hover { filter: brightness(1.08); }
        button:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
        .footer {
            text-align: center;
            margin: 22px 0 0;
            font-size: 0.9rem;
            color: var(--text-subtitle);
        }
        .footer a { color: var(--link); text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
        .error, .success {
            font-size: 0.85rem;
            border-radius: 10px;
            padding: 10px 14px;
            margin: 0 0 14px;
        }
        .error { color: var(--error); background: rgba(248, 113, 113, 0.08); }
        .success { color: var(--success); background: rgba(74, 222, 128, 0.08); }
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" style="color: var(--link); text-decoration: none; font-size: 0.9rem; display: inline-block; margin-bottom: 16px; font-weight: 500;">&larr; Back to Dashboard</a>
        <h1>Welcome Back</h1>
        <p class="subtitle">Enter your credentials to manage your posts</p>

        <?php if (!empty($success_msg)): ?>
            <div class="success">✅ <?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="error">⚠ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <form method="POST" action="login.php">
            <?= secure_csrf_field() ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <button type="submit">Log In</button>
        </form>

        <p class="footer">Don't have an account yet? <a href="register.php">Register</a></p>
    </div>
</body>
</html>

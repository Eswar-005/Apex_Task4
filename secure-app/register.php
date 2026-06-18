<?php
/**
 * secure-app/register.php
 *
 * Demonstrates defense-in-depth: HTML5/JS validation for UX, plus
 * mandatory server-side validation + sanitization, plus a prepared
 * INSERT so the data layer can never be reached with raw input.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_secure_authenticated()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_secure_csrf_token($token)) {
        $errors[] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        // ---- SERVER-SIDE VALIDATION ----
        // Never trust the client. JS validation can be disabled; this is
        // the layer that actually matters.
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Escape for safe re-display in the form (prevents reflected XSS).
        $old['username'] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            $errors[] = 'Username must be 3-20 characters: letters, numbers, underscores only.';
        }

        if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include a letter and a number.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        $role = trim($_POST['role'] ?? 'user');
        if (!in_array($role, ['user', 'editor', 'admin'])) {
            $role = 'user';
        }

        if (empty($errors)) {
            $pdo = getDbConnection();

            // Prepared statement - structure and data travel separately.
            $check = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $check->execute(['username' => $username]);

            if ($check->fetch()) {
                $errors[] = 'Username is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);

                $insert = $pdo->prepare(
                    'INSERT INTO users (username, password, role) VALUES (:username, :hash, :role)'
                );
                $insert->execute([
                    'username' => $username,
                    'hash'     => $hash,
                    'role'     => $role,
                ]);

                set_secure_flash_message('success', 'Registration successful! Please log in.');
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Secure Application Demo</title>
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
        input:focus, select:focus {
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
        .error {
            font-size: 0.85rem;
            border-radius: 10px;
            padding: 10px 14px;
            margin: 0 0 14px;
            color: var(--error);
            background: rgba(248, 113, 113, 0.08);
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" style="color: var(--link); text-decoration: none; font-size: 0.9rem; display: inline-block; margin-bottom: 16px; font-weight: 500;">&larr; Back to Dashboard</a>
        <h1>Create Account</h1>
        <p class="subtitle">Sign up to start managing your posts</p>

        <?php foreach ($errors as $error): ?>
            <div class="error">⚠ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <!-- Client-side validation (UX only, never trusted server-side) -->
        <form method="POST" action="register.php">
            <?= secure_csrf_field() ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required minlength="3" maxlength="20"
                   pattern="[A-Za-z0-9_]+" title="Letters, numbers, underscores only"
                   value="<?= $old['username'] ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required minlength="8"
                   pattern="(?=.*[0-9])(?=.*[A-Za-z]).{8,}"
                   title="At least 8 characters, including one letter and one number">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter Password" required minlength="8">

            <label for="role">Role</label>
            <select id="role" name="role" required style="width: 100%; padding: 13px 16px; border-radius: 12px; border: 1px solid rgba(139, 92, 246, 0.15); background: var(--input-bg); color: var(--text-heading); font-size: 0.95rem; font-family: inherit; transition: all 0.2s ease;">
                <option value="user">User (Read-only)</option>
                <option value="editor">Editor (Write posts)</option>
                <option value="admin">Admin (Full access)</option>
            </select>

            <button type="submit">Register</button>
        </form>

        <p class="footer">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>

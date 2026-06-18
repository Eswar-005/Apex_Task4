<?php
// seed_logins_all.php - Utility script to ensure test logins are seeded in both databases

$databases = [
    'blog' => 'mysql:host=127.0.0.1;port=3307;dbname=blog;charset=utf8mb4',
    'blog_secure' => 'mysql:host=127.0.0.1;port=3307;dbname=blog_secure;charset=utf8mb4'
];

$users = [
    ['username' => 'admin_user', 'password' => 'AdminSecurePass99!', 'role' => 'admin'],
    ['username' => 'editor_user', 'password' => 'EditorSecurePass99!', 'role' => 'editor'],
    ['username' => 'test_user', 'password' => 'UserSecurePass99!', 'role' => 'user'],
    ['username' => 'developer', 'password' => 'DeveloperPass99!', 'role' => 'user']
];

foreach ($databases as $dbName => $dsn) {
    echo "--- Seeding Database: {$dbName} ---\n";
    try {
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        foreach ($users as $u) {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$u['username']]);
            $row = $stmt->fetch();
            
            $hashed = password_hash($u['password'], PASSWORD_BCRYPT);
            
            if (!$row) {
                // Insert new user
                $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $insert->execute([$u['username'], $hashed, $u['role']]);
                echo "Successfully created {$u['username']} as {$u['role']}.\n";
            } else {
                // Update password and role to ensure they match credentials
                $update = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE username = ?");
                $update->execute([$hashed, $u['role'], $u['username']]);
                echo "Successfully updated {$u['username']} credentials (set role: {$u['role']}).\n";
            }
        }
    } catch (PDOException $e) {
        echo "Error in database {$dbName}: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

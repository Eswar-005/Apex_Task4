<?php
require 'config.php';

$users = [
    ['username' => 'admin_user', 'password' => 'Pass123', 'role' => 'admin'],
    ['username' => 'editor_user', 'password' => 'Pass123', 'role' => 'editor'],
    ['username' => 'test_user', 'password' => 'Pass123', 'role' => 'user']
];

foreach ($users as $u) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$u['username']]);
        if (!$stmt->fetch()) {
            $hashed = password_hash($u['password'], PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $insert->execute([$u['username'], $hashed, $u['role']]);
            echo "Created {$u['username']} ({$u['role']})\n";
        } else {
            echo "User {$u['username']} already exists.\n";
        }
    } catch (PDOException $e) {
        echo "Error creating {$u['username']}: " . $e->getMessage() . "\n";
    }
}

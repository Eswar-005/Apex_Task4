<?php
require 'config.php';

try {
    $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` ENUM('user', 'editor', 'admin') DEFAULT 'user'");
    echo "Added role column.\n";
} catch (PDOException $e) {
    echo "Column might already exist: " . $e->getMessage() . "\n";
}

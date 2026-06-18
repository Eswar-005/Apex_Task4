<?php
require 'config.php';

$sql = file_get_contents('setup.sql');
try {
    $pdo->exec($sql);
    echo "Database setup successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

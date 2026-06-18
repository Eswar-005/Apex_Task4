<?php
// tests/sqli_proof.php - SQL Injection Demonstration and Proof Script utilizing portable SQLite
// This script runs autonomously without needing an active external MySQL connection.

// Check if run via CLI or Web browser
if (php_sapi_name() !== 'cli') {
    echo "<pre style='background: #0f172a; color: #cbd5e1; padding: 20px; font-family: monospace; border-radius: 8px; line-height: 1.5;'>";
}

echo "=================================================================\n";
echo "           DEVBLOG SQL INJECTION (SQLi) DEMONSTRATION            \n";
echo "=================================================================\n\n";

// Input payload designed to bypass username verification or match multiple users
$userInput = "admin_jane' OR '1'='1";

echo "Simulated Attack Input Payload: " . $userInput . "\n\n";

try {
    // Construct a portable in-memory SQLite connection for testing
    $sqlitePdo = new PDO('sqlite::memory:');
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set up table
    $sqlitePdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTO_INCREMENT,
        username TEXT NOT NULL UNIQUE,
        role TEXT NOT NULL
    )");

    // Insert test users - Jane and a secondary user so multiple rows are leaked on bypass
    $sqlitePdo->exec("INSERT INTO users (username, role) VALUES ('admin_jane', 'admin')");
    $sqlitePdo->exec("INSERT INTO users (username, role) VALUES ('editor_user', 'editor')");
    $sqlitePdo->exec("INSERT INTO users (username, role) VALUES ('test_user', 'user')");

} catch (Exception $e) {
    // If the local SQLite version doesn't support AUTO_INCREMENT or runs into syntax issues, use standard INTEGER PRIMARY KEY
    try {
        $sqlitePdo = new PDO('sqlite::memory:');
        $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlitePdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            username TEXT NOT NULL UNIQUE,
            role TEXT NOT NULL
        )");
        $sqlitePdo->exec("INSERT INTO users (id, username, role) VALUES (1, 'admin_jane', 'admin')");
        $sqlitePdo->exec("INSERT INTO users (id, username, role) VALUES (2, 'editor_user', 'editor')");
        $sqlitePdo->exec("INSERT INTO users (id, username, role) VALUES (3, 'test_user', 'user')");
    } catch (PDOException $ex) {
        die("Error creating temporary SQLite test database: " . $ex->getMessage() . "\n");
    }
}

// --- DEMO 1: VULNERABLE DIRECT CONCATENATION QUERY ---
echo "--- DEMO 1: Vulnerable Direct Concatenation ---\n";
$vulnerableQuery = "SELECT id, username, role FROM users WHERE username = '" . $userInput . "'";
echo "Query: " . $vulnerableQuery . "\n";

try {
    // Vulnerable execution (simulates interpolation)
    $stmt = $sqlitePdo->query($vulnerableQuery);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Status: 🔴 BREACHED!\n";
    echo "Records Leaked from Database:\n";
    foreach ($results as $row) {
        echo "  - [ID: " . $row['id'] . "] Username: " . $row['username'] . " | Role: " . $row['role'] . "\n";
    }
} catch (PDOException $e) {
    echo "Status: Execution Failed (Query error: " . $e->getMessage() . ")\n";
}

echo "\n-----------------------------------------------------------------\n\n";

// --- DEMO 2: SECURED PREPARED STATEMENT QUERY ---
echo "--- DEMO 2: Secured Prepared Statement (PDO) ---\n";
echo "Query: SELECT id, username, role FROM users WHERE username = ? (Param bound as string)\n";

try {
    // Standard secure prepared statement
    $stmt = $sqlitePdo->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute([$userInput]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "Status: 🟢 SECURE! No records matched. The database safely treated the payload as a literal string.\n";
    } else {
        echo "Status: ⚠️ Records Matched:\n";
        foreach ($results as $row) {
            echo "  - [ID: " . $row['id'] . "] Username: " . $row['username'] . " | Role: " . $row['role'] . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Status: Execution Failed (Query error: " . $e->getMessage() . ")\n";
}

echo "\n=================================================================\n";
if (php_sapi_name() !== 'cli') {
    echo "</pre>";
}

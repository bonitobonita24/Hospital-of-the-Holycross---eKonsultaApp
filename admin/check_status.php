<?php
// Simple status checker
header('Content-Type: text/plain');

echo "=== eKonsulta Status Check ===\n\n";

// Check config.ini
$configPath = __DIR__ . '/../config.ini';
if (file_exists($configPath)) {
    $ini = parse_ini_file($configPath);
    echo "Config found: {$configPath}\n";
    echo "DB Host: " . ($ini['DBHOST'] ?? 'not set') . "\n";
    echo "DB Name: " . ($ini['DBNAME'] ?? 'not set') . "\n";
    echo "DB User: " . ($ini['DBUSER'] ?? 'not set') . "\n";
    echo "DB Schema: " . ($ini['EPCB'] ?? 'not set') . "\n\n";
} else {
    echo "ERROR: config.ini not found!\n\n";
}

// Check environment
echo "ENV_TYPE: " . (getenv('ENV_TYPE') ?: 'not set') . "\n";
echo "MYSQL_HOST env: " . (getenv('MYSQL_HOST') ?: 'not set') . "\n\n";

// Test DB connection
echo "=== Database Connection Test ===\n";
try {
    $host = $ini['DBHOST'] ?? 'mysql';
    $db = $ini['DBNAME'] ?? 'konsulta';
    $user = $ini['DBUSER'] ?? 'root';
    $pass = $ini['DBPASS'] ?? 'roottoor';
    
    echo "Attempting connection to: {$host}/{$db} as {$user}\n";
    
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    
    echo "✓ Connection successful!\n";
    
    $tables = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{$db}'")->fetchColumn();
    echo "✓ Tables found: {$tables}\n";
    
} catch (Exception $e) {
    echo "✗ Connection FAILED: " . $e->getMessage() . "\n";
    echo "\nPossible issues:\n";
    echo "- Docker containers not running (check: docker compose ps)\n";
    echo "- config.ini pointing to wrong host (should be 'mysql' for Docker)\n";
    echo "- MySQL not initialized yet (wait 30s after docker compose up)\n";
}

echo "\n=== End Status Check ===\n";

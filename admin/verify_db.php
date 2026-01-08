<?php
// Simple database verification script
// Access via: http://localhost:8080/admin/verify_db.php

header('Content-Type: text/plain');

try {
    $pdo = new PDO('mysql:host=mysql;dbname=konsulta;charset=utf8', 'root', 'roottoor', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "=== MySQL Database Verification ===\n\n";
    
    // Table count
    $tables = (int)$pdo->query("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema='konsulta'")->fetch()['c'];
    echo "Total tables in 'konsulta' database: {$tables}\n\n";
    
    // Check key table
    $exists = $pdo->query("SHOW TABLES LIKE 'TSEKAP_TBL_HCI_PROFILE'")->fetch();
    echo "TSEKAP_TBL_HCI_PROFILE exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    if ($exists) {
        $row = $pdo->query("SELECT COUNT(*) AS rows_count FROM TSEKAP_TBL_HCI_PROFILE")->fetch();
        echo "TSEKAP_TBL_HCI_PROFILE row count: " . (int)$row['rows_count'] . "\n\n";
        
        // Sample data
        echo "Sample data (first 3 rows):\n";
        $sample = $pdo->query("SELECT * FROM TSEKAP_TBL_HCI_PROFILE LIMIT 3")->fetchAll();
        foreach ($sample as $idx => $record) {
            echo "  Row " . ($idx + 1) . ": " . json_encode($record, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\n=== List of Tables ===\n";
    $tableList = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tableList as $idx => $table) {
        echo ($idx + 1) . ". {$table}\n";
    }
    
    echo "\n=== Verification Complete ===\n";
    echo "Status: OK\n";
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection error: " . $e->getMessage() . "\n";
    echo "Make sure MySQL container is running: docker compose ps\n";
}

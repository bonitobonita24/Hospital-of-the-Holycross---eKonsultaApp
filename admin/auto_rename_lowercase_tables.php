<?php
/**
 * Auto-detect and rename all lowercase tables to UPPERCASE
 * This script will:
 * 1. Find all tables in the database
 * 2. Identify which ones have lowercase characters
 * 3. Generate RENAME TABLE statements for those
 * 4. Execute the renames
 */

// Security token - change this for your environment
$token = $_GET['token'] ?? '';
$expected_token = 'rename_tables_2025';
$execute = $_GET['execute'] ?? false;

if ($token !== $expected_token) {
    die('Invalid token. Access denied.');
}

// Load config
$config_file = dirname(__DIR__) . '/config.ini';
if (!file_exists($config_file)) {
    die('Config file not found');
}

$ini = parse_ini_file($config_file);

// Connect to database
try {
    $dsn = "mysql:host=" . $ini['DBSERVER'] . ";port=" . $ini['DBPORT'] . ";dbname=" . $ini['EPCB'];
    $pdo = new PDO($dsn, $ini['DBUSERID'], $ini['DBPWD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get all tables
$stmt = $pdo->query("SHOW TABLES");
$all_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Identify lowercase tables
$lowercase_tables = [];
$uppercase_tables = [];
$mixed_tables = [];

foreach ($all_tables as $table) {
    if ($table === strtoupper($table)) {
        $uppercase_tables[] = $table;
    } elseif ($table === strtolower($table)) {
        $lowercase_tables[] = $table;
    } else {
        $mixed_tables[] = $table;
    }
}

// Generate RENAME statements
$rename_statements = [];
foreach ($lowercase_tables as $table) {
    $uppercase_table = strtoupper($table);
    $rename_statements[] = "RENAME TABLE `$table` TO `$uppercase_table`";
}

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auto Rename Lowercase Tables</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .stats { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .stats h3 { margin-top: 0; }
        .stats p { margin: 5px 0; }
        .tables-list { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; max-height: 400px; overflow-y: auto; }
        .tables-list h4 { margin-top: 0; }
        .tables-list ul { margin: 10px 0; padding-left: 20px; }
        .tables-list li { margin: 3px 0; font-family: monospace; }
        .rename-statements { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; max-height: 500px; overflow-y: auto; }
        .rename-statements h4 { margin-top: 0; }
        .rename-statements code { display: block; background: #f5f5f5; padding: 8px; margin: 5px 0; border-left: 3px solid #007bff; }
        .button-group { margin-bottom: 20px; }
        button { padding: 10px 20px; margin-right: 10px; cursor: pointer; border: none; border-radius: 3px; font-size: 14px; }
        .btn-execute { background: #28a745; color: white; }
        .btn-execute:hover { background: #218838; }
        .btn-copy { background: #007bff; color: white; }
        .btn-copy:hover { background: #0056b3; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 3px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        #output { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Auto Rename Lowercase Tables to UPPERCASE</h2>

    <div class="stats">
        <h3>Database Statistics</h3>
        <p><strong>Database:</strong> <?php echo htmlspecialchars($ini['EPCB']); ?></p>
        <p><strong>Total Tables:</strong> <?php echo count($all_tables); ?></p>
        <p><strong>Already UPPERCASE:</strong> <?php echo count($uppercase_tables); ?></p>
        <p><strong>LOWERCASE (need renaming):</strong> <span style="color: #d9534f; font-weight: bold;"><?php echo count($lowercase_tables); ?></span></p>
        <p><strong>Mixed case:</strong> <?php echo count($mixed_tables); ?></p>
    </div>

    <?php if (count($lowercase_tables) > 0): ?>
        <div class="tables-list">
            <h4>Lowercase Tables to be Renamed (<?php echo count($lowercase_tables); ?>)</h4>
            <ul>
                <?php foreach ($lowercase_tables as $table): ?>
                    <li><?php echo htmlspecialchars($table); ?> → <?php echo htmlspecialchars(strtoupper($table)); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="rename-statements">
            <h4>SQL Statements</h4>
            <p>Total statements to execute: <strong><?php echo count($rename_statements); ?></strong></p>
            <?php foreach ($rename_statements as $stmt): ?>
                <code><?php echo htmlspecialchars($stmt); ?>;</code>
            <?php endforeach; ?>
        </div>

        <div class="button-group">
            <button class="btn-execute" onclick="executeRename()">Execute Rename NOW</button>
            <button class="btn-copy" onclick="copyToClipboard()">Copy SQL to Clipboard</button>
        </div>

        <div id="output"></div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong>✓ All tables are already in UPPERCASE!</strong> No renaming needed.
        </div>
    <?php endif; ?>

    <?php if (count($uppercase_tables) > 0): ?>
        <div class="tables-list">
            <h4>Already UPPERCASE Tables (<?php echo count($uppercase_tables); ?>)</h4>
            <ul>
                <?php foreach (array_slice($uppercase_tables, 0, 10) as $table): ?>
                    <li><?php echo htmlspecialchars($table); ?></li>
                <?php endforeach; ?>
                <?php if (count($uppercase_tables) > 10): ?>
                    <li><em>...and <?php echo count($uppercase_tables) - 10; ?> more</em></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <script>
        function copyToClipboard() {
            const statements = [
                <?php foreach ($rename_statements as $stmt): ?>
                    "<?php echo addslashes($stmt); ?>;",
                <?php endforeach; ?>
            ];
            const text = statements.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('SQL statements copied to clipboard!');
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        }

        function executeRename() {
            if (!confirm('Are you sure you want to rename <?php echo count($lowercase_tables); ?> tables? This cannot be undone!')) {
                return;
            }

            const output = document.getElementById('output');
            output.innerHTML = '<p style="color: #007bff;"><strong>Executing renames...</strong></p>';

            fetch('<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>&execute=1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'execute=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    output.innerHTML = '<div class="alert alert-success"><strong>✓ Success!</strong> ' + data.message + '</div>';
                } else {
                    output.innerHTML = '<div class="alert alert-danger"><strong>✗ Error:</strong> ' + data.message + '</div>';
                }
            })
            .catch(error => {
                output.innerHTML = '<div class="alert alert-danger"><strong>✗ Error:</strong> ' + error.message + '</div>';
            });
        }
    </script>
</body>
</html>

<?php
// Handle POST execute request
if ($_POST['execute'] ?? false) {
    header('Content-Type: application/json');
    
    try {
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($rename_statements as $stmt) {
            try {
                $pdo->exec($stmt);
                $successful++;
            } catch (PDOException $e) {
                $failed++;
                $errors[] = $stmt . " - Error: " . $e->getMessage();
            }
        }

        if ($failed === 0) {
            echo json_encode([
                'success' => true,
                'message' => "$successful tables successfully renamed to UPPERCASE!"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "$successful successful, $failed failed. Errors: " . implode("; ", $errors)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>

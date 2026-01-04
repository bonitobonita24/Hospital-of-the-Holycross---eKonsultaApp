<?php
/**
 * Environment Configuration Switcher
 * Switches between development (Docker) and production (cPanel) environments
 * 
 * Usage:
 *   php setenv.php dev       - Use Docker development environment
 *   php setenv.php prod      - Use cPanel production environment (default)
 *   php setenv.php status    - Show current environment
 * 
 * Default: Production (for cPanel shared hosting)
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script is for CLI use only.\n");
    exit(1);
}

$env = (isset($argv[1]) && $argv[1] !== '') ? $argv[1] : 'status';
$allowed = ['dev', 'prod', 'production', 'development', 'status'];

if (!in_array($env, $allowed, true)) {
    fwrite(STDERR, "Usage: php setenv.php [dev|prod|development|production|status]\n");
    exit(1);
}

// Normalize environment names
$env = str_replace('production', 'prod', str_replace('development', 'dev', $env));

$source = __DIR__ . "/config.$env.ini";
$target = __DIR__ . '/config.ini';

// Status check - show current environment
if ($env === 'status') {
    if (file_exists($target)) {
        $config = parse_ini_file($target);
        $envType = isset($config['ENV_TYPE']) ? $config['ENV_TYPE'] : 'unknown';
        $server = isset($config['DBSERVER']) ? $config['DBSERVER'] : 'unknown';
        echo "✓ Current Environment: $envType\n";
        echo "  Database Server: $server\n";
    } else {
        echo "✗ No active configuration found\n";
    }
    exit(0);
}

// Switch environment
if (!file_exists($source)) {
    fwrite(STDERR, "Config template not found: $source\n");
    exit(1);
}

if (!copy($source, $target)) {
    fwrite(STDERR, "Failed to write $target\n");
    exit(1);
}

$config = parse_ini_file($target);
$envType = isset($config['ENV_TYPE']) ? $config['ENV_TYPE'] : $env;
$server = isset($config['DBSERVER']) ? $config['DBSERVER'] : 'unknown';

echo "✓ Environment switched to: $envType\n";
echo "  Database Server: $server\n";
echo "  Config File: config.ini (active)\n";


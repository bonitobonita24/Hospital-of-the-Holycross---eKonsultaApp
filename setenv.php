<?php
// CLI-only helper to switch between dev/prod configs by copying config.<env>.ini to config.ini

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script is for CLI use only.\n");
    exit(1);
}

$env = $argv[1] ?? '';
$allowed = ['dev', 'prod'];

if (!in_array($env, $allowed, true)) {
    fwrite(STDERR, "Usage: php setenv.php [dev|prod]\n");
    exit(1);
}

$source = __DIR__ . "/config.$env.ini";
$target = __DIR__ . '/config.ini';

if (!file_exists($source)) {
    fwrite(STDERR, "Config template not found: $source\n");
    exit(1);
}

if (!copy($source, $target)) {
    fwrite(STDERR, "Failed to write $target\n");
    exit(1);
}

echo "Environment set to $env. Active config: config.ini\n";

<?php

$content = '<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <[email protected]>
 */

$uri = urldecode(
    parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH)
);

// This file allows us to emulate Apache\'s "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== \'/\' && file_exists(__DIR__.\'/public\'.$uri)) {
    return false;
}

require_once __DIR__.\'/public/index.php\';
';

$result = @file_put_contents('server.php', $content);

if ($result !== false) {
    echo "SUCCESS: server.php created with " . $result . " bytes\n";
} else {
    $error = error_get_last();
    echo "FAILED: Could not create server.php\n";
    echo "Error: " . ($error['message'] ?? 'Unknown error') . "\n";
    echo "\nPlease try:\n";
    echo "1. Run PowerShell/CMD as Administrator\n";
    echo "2. Check if antivirus is blocking file creation\n";
    echo "3. Manually create server.php with the content above\n";
}






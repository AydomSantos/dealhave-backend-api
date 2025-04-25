
<?php
// Check if session is already active before trying to modify settings
if (session_status() == PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'del_teste');

// URL configuration
define('BASE_URL', 'http://localhost/Plataforma-de-economia-compartilhada');

// Timezone settings
date_default_timezone_set('America/Sao_Paulo');

// Error display settings (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Global utility functions
function redirect($url) {
    header("Location: $url");
    exit;
}
?>

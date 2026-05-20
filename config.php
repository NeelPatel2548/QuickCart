<?php
/**
 * QuickCart - Database Configuration File
 * This file establishes the connection to the MySQL database.
 */

// Database Credentials
$host     = 'localhost';
$db_name  = 'quickcart_db';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

// Set Options for PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 3, // Fail fast if MySQL is unreachable
];

try {
    // Create the connection
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If connection fails, stop and show error
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Start User Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define Base URL — auto-detect from the current request
$_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost:3000';
define('BASE_URL', $_protocol . '://' . $_host . '/');
define('SITE_NAME', 'QuickCart');

// Load CSRF protection helper
require_once __DIR__ . '/includes/csrf_helper.php';

/**
 * Shorthand XSS-safe output helper.
 * Use e($var) instead of htmlspecialchars($var, ENT_QUOTES, 'UTF-8') everywhere.
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message system — survives one redirect via session.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>


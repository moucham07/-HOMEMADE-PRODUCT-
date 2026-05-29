<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db_name = 'homemade_marketplace';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // For production, log error instead of displaying it
    die("Connection failed: " . $e->getMessage());
}

// Cache website settings
$site_settings = [];
try {
    $stmt_s = $conn->query("SELECT `key`, `value` FROM settings");
    while ($row = $stmt_s->fetch()) {
        $site_settings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {
    // Settings table might not exist yet during migration
}

// Helper to retrieve site setting
function getSetting($key, $default = '') {
    global $site_settings;
    return isset($site_settings[$key]) ? $site_settings[$key] : $default;
}

// Helper to get currency symbol
function getCurrencySymbol() {
    $currency = getSetting('currency', 'INR');
    switch ($currency) {
        case 'USD': return '$';
        case 'EUR': return '€';
        case 'GBP': return '£';
        default: return '₹';
    }
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Redirect helpers
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>

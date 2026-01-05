<?php
/**
 * Configuration File for Money Exchange System
 * Diamond Group Money Exchange
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Baghdad');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'exchange_system');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Diamond Money Exchange');
define('APP_NAME_KU', 'گۆڕینی پارەی دایمۆند');
define('APP_NAME_AR', 'صرافة الماس');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/exchange_system/');

// Security
define('ENCRYPTION_KEY', 'DiamondExchange2024SecureKey!@#');
define('SESSION_LIFETIME', 3600); // 1 hour

// Pagination
define('RECORDS_PER_PAGE', 20);

// Currency Settings
define('DEFAULT_CURRENCY', 'IQD');
define('SECONDARY_CURRENCY', 'USD');
define('CURRENCY_PRECISION', 2);

// File Upload Settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Export Settings
define('EXPORT_PATH', __DIR__ . '/../exports/');

// Default Language
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'ku'; // Kurdish Sorani as default
}

// Database Connection Function
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Get current language
function getCurrentLanguage() {
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'ku';
}

// Set language
function setLanguage($lang) {
    if (in_array($lang, ['en', 'ku', 'ar'])) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

// Get user permissions
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (isAdmin()) {
        return true; // Admin has all permissions
    }
    
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    
    $permissions = json_decode($_SESSION['permissions'], true);
    return isset($permissions[$permission]) && $permissions[$permission] === true;
}

// Log activity
function logActivity($action, $description = '', $user_id = null) {
    $db = getDBConnection();
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip, $user_agent]);
}

// Format currency
function formatCurrency($amount, $currency_code = 'IQD') {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT symbol FROM currencies WHERE code = ?");
    $stmt->execute([$currency_code]);
    $result = $stmt->fetch();
    
    $symbol = $result ? $result['symbol'] : '';
    return number_format($amount, CURRENCY_PRECISION) . ' ' . $symbol;
}

// Format date
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate unique receipt number
function generateReceiptNumber($prefix = 'TR') {
    return $prefix . date('Ymd') . rand(1000, 9999);
}

// Get setting value
function getSetting($key, $default = null) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

// Update setting value
function updateSetting($key, $value) {
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

// Get exchange rate
function getExchangeRate($from_currency, $to_currency = 'IQD') {
    $db = getDBConnection();
    
    if ($from_currency === $to_currency) {
        return 1.0;
    }
    
    $stmt = $db->prepare("SELECT exchange_rate_to_iqd FROM currencies WHERE code = ?");
    $stmt->execute([$from_currency]);
    $result = $stmt->fetch();
    
    return $result ? (float)$result['exchange_rate_to_iqd'] : 1.0;
}

// Convert currency
function convertCurrency($amount, $from_currency, $to_currency = 'IQD') {
    if ($from_currency === $to_currency) {
        return $amount;
    }
    
    $rate = getExchangeRate($from_currency, $to_currency);
    return $amount * $rate;
}

// Flash message functions
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Check database connection on load
try {
    getDBConnection();
} catch (Exception $e) {
    // Database connection will be attempted when needed
}

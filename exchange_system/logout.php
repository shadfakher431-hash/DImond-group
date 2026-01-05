<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    logActivity('logout', 'User logged out');
}

// Clear all session data
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;

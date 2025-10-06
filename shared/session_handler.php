<?php
// Prevent multiple inclusions
if (defined('SESSION_HANDLER_LOADED')) {
    return;
}
define('SESSION_HANDLER_LOADED', true);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set user variables for the application
$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'John Doe';
$user_email = $_SESSION['user_email'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_image = $_SESSION['user_image'] ?? '../assets/img/avatars/default.png';


// Simple auth check function that doesn't depend on config.php
if (!function_exists('checkAuth')) {
    function checkAuth() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('requireUserAuth')) {
    function requireUserAuth() {
        if (!checkAuth()) {
            header('Location: ../../login.php');
            exit();
        }
    }
}
?>

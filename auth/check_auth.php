<?php
/**
 * Authentication Middleware
 * Include this file at the top of every protected page
 * Usage: require_once '../auth/check_auth.php';
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/auth/') !== false ? '../' : '') . 'auth/login.php');
    exit();
}

// Optional: Define user context globally
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$user_name = $_SESSION['user_name'];

/**
 * Check if user has admin role
 * Usage: if (!is_admin()) { die("Access denied"); }
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user has kasir role
 * Usage: if (!is_kasir()) { die("Access denied"); }
 */
function is_kasir() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}

/**
 * Require admin role
 * Usage: require_admin();
 */
function require_admin() {
    if (!is_admin()) {
        header('Location: ../dashboard/index.php?error=access_denied');
        exit();
    }
}

/**
 * Require kasir role
 * Usage: require_kasir();
 */
function require_kasir() {
    if (!is_kasir()) {
        header('Location: ../dashboard/index.php?error=access_denied');
        exit();
    }
}

// Redirect function for relative paths
function safe_redirect($url) {
    // Remove any leading slashes for consistency
    $url = ltrim($url, '/');
    // Determine if we need to go up directories
    $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
    $prefix = str_repeat('../', max(0, $depth - 2));
    header('Location: ' . $prefix . $url);
    exit();
}
?>

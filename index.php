<?php
// Start session
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Redirect based on user role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'customer') {
        header("Location: customer_dashboard.php");
        exit;
    }
}

// Default fallback (if role is missing or unrecognized)
header("Location: login.php");
exit;
?>

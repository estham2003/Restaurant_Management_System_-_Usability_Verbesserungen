<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Fetch username for greeting
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin');

// Handle logout request
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .dashboard-header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .dashboard-header .logout-btn {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 5px 10px;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }

        .dashboard-header .logout-btn:hover {
            background-color: #bd2130;
        }

        .dashboard-main {
            padding: 30px;
        }

        .dashboard-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .dashboard-link {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            width: 220px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dashboard-link:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .dashboard-link img {
            width: 50px;
            margin-bottom: 15px;
        }

        .dashboard-link h3 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: #343a40;
            color: white;
            margin-top: 19%;
        }
    </style>
</head>

<header class="dashboard-header">
    <h1>Welcome, <?php echo $username; ?>!</h1>
    <a href="?logout" class="logout-btn">Logout</a>
</header>

<body>

    <main class="dashboard-main">
        <div class="dashboard-links">
            <a href="bookings.php" class="dashboard-link">
                <img src="icons/booking.png" alt="Manage Bookings">
                <h3>Manage Bookings</h3>
            </a>    
            <div class="dashboard-link">
                <img src="icons/list.png" alt="Manage Menu">
                <h3>Manage Menu</h3>
                <a href="manage_menu.php" class="btn btn-info btn-sm">Manage Menu</a>
            </div>
            <div class="dashboard-link">
                <img src="icons/user.png" alt="Manage Users">
                <h3>Manage Users</h3>
                <a href="manage_users.php" class="btn btn-secondary btn-sm">Manage Users</a>
            </div>
            <div class="dashboard-link">
                <img src="icons/table.png" alt="Manage Tables">
                <h3>Manage Tables</h3>
                <a href="manage_table.php" class="btn btn-danger btn-sm">Manage Tables</a>
            </div>
        </div>
    </main>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

<footer>
    &copy; 2024 Admin Dashboard. All rights reserved.
</footer>

</html>
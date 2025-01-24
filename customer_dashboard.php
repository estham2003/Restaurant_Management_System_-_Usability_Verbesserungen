<?php
// Enable error reporting for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect to login page if not logged in or if the role is not 'customer'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'customer') {
    header('location: login.php');
    exit;
}

// Include the database configuration file
require_once 'config.php';

// Retrieve the username from the session
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://lafrodisiacrestaurant.fr/wp-content/uploads/2021/02/LAdrodisiac-Restaurant-Photo-3-2048x1365.jpg');
            background-color: #f8f9fa;
            background-color: #f8f9fa;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #f8f9fa;

        }

        .dashboard-header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            position: relative;
        }

        .profile-dropdown {
            position: absolute;
            top: 10px;
            right: 20px;
            text-align: right;
        }

        .dropdown-menu {
            text-align: left;
            min-width: 200px;
        }

        .dropdown-item.logout {
            color: red;
            font-weight: bold;
        }

        .dropdown-item.logout:hover {
            background-color: #f8d7da;
            color: darkred;
        }

        .dashboard-main {
            padding: 40px;
            min-height: calc(100vh - 160px);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
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
            transition: transform 0.2s;
        }

        .dashboard-link:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .dashboard-link i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #17a2b8;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <div class="profile-dropdown">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Profile
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="profile.php">Update Profile</a></li>
                    <li><a class="dropdown-item" href="upload_picture.php">Upload Profile Picture</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item logout" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Main Dashboard Section -->
    <main class="dashboard-main">
        <div class="dashboard-links">
            <div class="dashboard-link">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                <h3>View Menu</h3>
                <a href="menu.php" class="btn btn-primary" role="button" aria-label="Go to Menu">Explore</a>
            </div>
            <div class="dashboard-link">
                <i class="fas fa-history" aria-hidden="true"></i>
                <h3>Order History</h3>
                <a href="order_history.php" class="btn btn-primary" role="button"
                    aria-label="View Order History">View</a>
            </div>
            <div class="dashboard-link">
                <i class="fas fa-comments" aria-hidden="true"></i>
                <h3>Comments</h3>
                <a href="comment.php" class="btn btn-primary" role="button" aria-label="Go to Comments">View
                    Comments</a>
            </div>

        
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Customer Dashboard. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JavaScript Bundle for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

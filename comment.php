<?php
session_start();
require_once 'config.php';

$link = getDB(); // Database connection

// Fetch all comments with corresponding user and order information
$sql = "
    SELECT c.comment_id, c.comment, c.created_at, c.rating, u.username, o.menu_fk, m.name AS menu_name
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    JOIN orders o ON c.order_id = o.order_id
    JOIN menu m ON o.menu_fk = m.menu_id
    ORDER BY c.created_at DESC
";

$result = mysqli_query($link, $sql);

// Determine the dashboard URL based on user role
$dashboardUrl = isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'customer_dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://lafrodisiacrestaurant.fr/wp-content/uploads/2021/02/LAdrodisiac-Restaurant-Photo-3-2048x1365.jpg');
            background-color: #f8f9fa;
        }

        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            background-color: #007bff;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }

        .header a:hover {
            background-color: #0056b3;
        }

        .table-responsive {
            margin-top: 30px;
        }

        .empty-message {
            margin: 40px 0;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="header">
        <h1>User Comments</h1>
        <!-- Dynamic Back to Dashboard Link -->
        <a href="<?= htmlspecialchars($dashboardUrl); ?>" class="btn btn-outline-light">Back to Dashboard</a>
    </header>

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="text-center mb-4">Comments Overview</h3>

                <div class="table-responsive">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Comment ID</th>
                                    <th>Username</th>
                                    <th>Menu Item</th>
                                    <th>Comment</th>
                                    <th>Rating</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['comment_id']); ?></td>
                                        <td><?= htmlspecialchars($row['username']); ?></td>
                                        <td><?= htmlspecialchars($row['menu_name']); ?></td>
                                        <td><?= htmlspecialchars($row['comment']); ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $row['rating']): ?>
                                                    <span class="text-warning">&#9733;</span> <!-- Filled star -->
                                                <?php else: ?>
                                                    <span class="text-muted">&#9734;</span> <!-- Empty star -->
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </td>
                                        <td><?= date("F j, Y, g:i A", strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="empty-message">No comments available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
mysqli_close($link);
?>

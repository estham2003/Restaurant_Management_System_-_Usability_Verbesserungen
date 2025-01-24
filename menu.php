<?php
session_start();
require_once 'config.php';

// Validate user session
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection function with error handling
function getDbConnection()
{
    $link = getDB();
    if (!$link) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die("Database connection error. Please try again later.");
    }
    return $link;
}

// Fetch data with error handling
function fetchData($link, $query)
{
    $result = $link->query($query);
    if (!$result) {
        error_log("Query failed: " . $link->error);
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Validate and sanitize input
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input));
}

try {
    $link = getDbConnection();

    // Fetch categories
    $categories = fetchData($link, "SELECT category_id, name FROM categories");

    // Fetch tables with status
    $tables = fetchData($link, "
        SELECT t.table_id, 
               t.table_nr, 
               CASE WHEN EXISTS (
                   SELECT 1 
                   FROM orders o 
                   WHERE o.table_fk = t.table_id 
                     AND o.status = 'placed'
               ) THEN 'in_use' 
               ELSE 'available' 
               END AS status
        FROM tables t
    ");

    // Handle order submission
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order'])) {
        $userId = $_SESSION['user_id'] ?? 0;
        $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
        $tableId = filter_input(INPUT_POST, 'table_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$userId || !$itemId || !$tableId || !$quantity || $quantity <= 0) {
            throw new Exception("Invalid order details. Please check your input.");
        }

        $link->begin_transaction();

        try {
            // Insert into orders
            $orderSql = "INSERT INTO orders (user_fk, table_fk, menu_fk, quantity, status) 
                          VALUES (?, ?, ?, ?, 'placed')";
            $orderStmt = $link->prepare($orderSql);
            $orderStmt->bind_param('iiii', $userId, $tableId, $itemId, $quantity);

            if (!$orderStmt->execute()) {
                throw new Exception("Failed to create order: " . $orderStmt->error);
            }

            $orderId = $link->insert_id;

            // Insert booking into bookings
            $bookingTime = date('Y-m-d H:i:s');
            $bookingSql = "INSERT INTO bookings (order_fk, booking_time, status) 
                             VALUES (?, ?, 'confirmed')";
            $bookingStmt = $link->prepare($bookingSql);
            $bookingStmt->bind_param('is', $orderId, $bookingTime);

            if (!$bookingStmt->execute()) {
                throw new Exception("Failed to create booking: " . $bookingStmt->error);
            }

            // Update table status
            $tableUpdateSql = "UPDATE tables SET status = 'not' 
                                WHERE table_id = ? AND status = 'available'";
            $tableUpdateStmt = $link->prepare($tableUpdateSql);
            $tableUpdateStmt->bind_param('i', $tableId);
            $tableUpdateStmt->execute();

            $link->commit();
            $_SESSION['order_success'] = true;
            $_SESSION['last_order_id'] = $orderId;
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $link->rollback();
            $_SESSION['error_message'] = $e->getMessage();
        }
    }

    // Fetch menu items
    $menu = fetchData($link, "
        SELECT menu_id, image_url, name, description, price, category_id 
        FROM menu
    ");

} catch (Exception $e) {
    error_log("Fatal error: " . $e->getMessage());
    die("An unexpected error occurred. Please try again later.");
} finally {
    if (isset($link)) {
        $link->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restaurant Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --hover-scale: 1.05;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }

        header,
        footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
        }

        header h1,
        footer p {
            margin: 0;
        }

        .back-btn {
            background-color: #6c757d;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            color: #ffffff;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .menu-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            transform: scale(var(--hover-scale));
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .menu-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .menu-item:hover img {
            transform: scale(1.1);
        }

        .in-use {
            color: #dc3545;
            font-weight: bold;
        }

        .available {
            color: #28a745;
            font-weight: bold;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading {
            pointer-events: none;
            opacity: 0.7;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center">
        <h1 class="fs-3 ms-3">Afromix Restaurant</h1>
        <button class="btn btn-primary" role="button" onclick="history.back()">Back to dashboard</button>
    </header>

    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Main Content -->
    <main class="container my-4 flex-grow-1">
        <h2 class="text-center mb-4">Our Menu</h2>

        <!-- Display Errors -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= sanitizeInput($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Category Filter -->
        <select id="categoryFilter" class="form-select mb-4">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= sanitizeInput($category['category_id']); ?>">
                    <?= sanitizeInput($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Menu Grid -->
        <div class="menu-grid">
            <?php foreach ($menu as $item): ?>
                <div class="menu-item category-<?= sanitizeInput($item['category_id']); ?>">
                    <img src="<?= sanitizeInput($item['image_url']); ?>" alt="<?= sanitizeInput($item['name']); ?>" />
                    <div class="p-3">
                        <h3 class="h5"><?= sanitizeInput($item['name']); ?></h3>
                        <p class="text-muted"><?= sanitizeInput($item['description']); ?></p>
                        <p class="fw-bold">$<?= number_format($item['price'], 2); ?></p>

                        <!-- Order Form -->
                        <form class="order" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                            <input type="hidden" name="item_id" value="<?= sanitizeInput($item['menu_id']); ?>" />
                            <div class="mb-3">
                                <label for="table_id" class="form-label">Table No:</label>
                                <select name="table_id" class="form-select" required>
                                    <option value="">Select a table</option>
                                    <?php foreach ($tables as $table): ?>
                                        <option value="<?= sanitizeInput($table['table_id']); ?>">
                                            Table <?= sanitizeInput($table['table_nr']); ?> - 
                                            <span class="<?= $table['status'] === 'in_use' ? 'in-use' : 'available'; ?>">
                                                <?= ucfirst($table['status']); ?>
                                            </span>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" required />
                            </div>

                            <button type="submit" name="order" class="btn btn-primary w-100">
                                Pay Now
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Order Placed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Your order has been placed successfully and is ready for payment!</p>
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='order_history.php'">
                            Go to Order History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">&copy; <?= date('Y'); ?> Our Restaurant. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function () {
            // Category filter functionality
            $('#categoryFilter').on('change', function () {
                const categoryId = $(this).val();
                if (categoryId === '') {
                    $('.menu-item').show();
                } else {
                    $('.menu-item').hide();
                    $('.category-' + categoryId).show();
                }
            });

            // Form submission handling
            $('.order').on('submit', function (e) {
                $('.loading-overlay').css('display', 'flex');
                $('body').addClass('loading');
                return true;
            });
        });
    </script>

    <?php if (isset($_SESSION['order_success']) && $_SESSION['order_success']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            <?php unset($_SESSION['order_success']); ?>
            
            setTimeout(function() {
                window.location.href = 'order_history.php';
            }, 3000);
        });
    </script>
    <?php endif; ?>

</body>
</html>
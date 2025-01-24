<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$link = getDB();

// Function to calculate total order amount
function calculateOrderTotal($link, $order_id)
{
    $total_sql = "SELECT SUM(m.price * o.quantity) as total 
                  FROM orders o 
                  JOIN menu m ON o.menu_fk = m.menu_id 
                  WHERE o.order_id = ?";
    $stmt = mysqli_prepare($link, $total_sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Handle payment and comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'pay':
            $order_id = $_POST['order_id'];
            $payment_type = $_POST['payment_type'];
            $amount = calculateOrderTotal($link, $order_id);

            // Insert payment record
            $payment_sql = "INSERT INTO payments (order_id, amount, payment_type, payment_status) 
                            VALUES (?, ?, ?, ?)";
            $payment_status = ($payment_type == 'online') ? 'completed' : 'pending';

            $stmt = mysqli_prepare($link, $payment_sql);
            mysqli_stmt_bind_param($stmt, "idss", $order_id, $amount, $payment_type, $payment_status);
            mysqli_stmt_execute($stmt);

            // Update order status
            $update_order_sql = "UPDATE orders SET status = 'paid' WHERE order_id = ?";
            $stmt = mysqli_prepare($link, $update_order_sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);

            $_SESSION['success_message'] = "Payment processed successfully via $payment_type.";
            header('Location: order_history.php');
            exit;

        case 'comment':
            $order_id = $_POST['order_id'];
            $comment_text = trim($_POST['comment_text']);
            $rating = (int) $_POST['rating'];

            $insert_sql = "INSERT INTO comments (order_id, user_id, comment, rating) 
                           VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE comment = ?, rating = ?, created_at = CURRENT_TIMESTAMP";
            $stmt = mysqli_prepare($link, $insert_sql);
            mysqli_stmt_bind_param($stmt, "iisisi", $order_id, $_SESSION['user_id'], $comment_text, $rating, $comment_text, $rating);
            mysqli_stmt_execute($stmt);

            $_SESSION['success_message'] = "Comment submitted successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
    }
}

// Prepare SQL based on user role
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
    $sql = "SELECT 
                o.order_id, 
                o.order_time, 
                o.status, 
                u.username,
                SUM(m.price * o.quantity) as total_amount
            FROM orders o 
            JOIN users u ON o.user_fk = u.user_id
            JOIN menu m ON o.menu_fk = m.menu_id
            GROUP BY o.order_id";
} else {
    $sql = "SELECT 
                o.order_id, 
                o.order_time, 
                o.status,
                SUM(m.price * o.quantity) as total_amount
            FROM orders o 
            JOIN menu m ON o.menu_fk = m.menu_id
            WHERE o.user_fk = ?
            GROUP BY o.order_id";
}

$stmt = mysqli_prepare($link, $sql);
if ($_SESSION['role'] === 'customer') {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .modal-header,
        .modal-footer {
            justify-content: center;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="header d-flex justify-content-between align-items-center">
        <h1>Order Management</h1>
        <a href="customer_dashboard.php" class="btn btn-outline-light" style="margin-left: 10px;">Back to
            Dashboard</a>
    </header>
    <div class="container mt-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order Time</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th>User</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['order_time']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>$<?= number_format($row['total_amount'], 2) ?></td>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($row['status'] === 'placed'): ?>
                                    <button class="btn btn-primary btn-sm pay-btn" data-order-id="<?= $row['order_id'] ?>"
                                        data-total="<?= $row['total_amount'] ?>">Pay</button>
                                <?php endif; ?>
                                <?php if ($row['status'] === 'paid'): ?>
                                    <button class="btn btn-secondary btn-sm comment-btn"
                                        data-order-id="<?= $row['order_id'] ?>">Comment</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modals for Payment and Comment -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" method="POST">
                        <input type="hidden" name="action" value="pay">
                        <input type="hidden" name="order_id" id="payOrderId">
                        <div class="mb-3">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" id="paymentTotal" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Payment Method</label>
                            <div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_type" value="cash" required>
        <label class="form-check-label">
        
            <img src="https://www.grazing.nz/wp-content/uploads/2019/12/Money-image-adjustment-01-1024x986.png" alt="Cash" style="width: 50px; height: auto;">
            <label class="form-check-label">( Cash )</label>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_type" value="online" required>
        <label class="form-check-label">
        <img src="https://th.bing.com/th/id/OIP.wBKSzdf1HTUgx1Ax_EecKwHaHa?rs=1&pid=ImgDetMain" alt="Online" style="width: 50px; height: auto;">
        <label class="form-check-label">( Online )</label>
        </label>
    </div>
</div>

                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Confirm Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="commentForm" method="POST">
                        <input type="hidden" name="action" value="comment">
                        <input type="hidden" name="order_id" id="commentOrderId">
                        <div class="mb-3">
                            <label>Your Comment</label>
                            <textarea class="form-control" name="comment_text" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Rating</label>
                            <select class="form-select" name="rating" required>
                                <option value="">Select Rating</option>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Comment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            const commentModal = new bootstrap.Modal(document.getElementById('commentModal'));

            document.querySelectorAll('.pay-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('payOrderId').value = this.getAttribute('data-order-id');
                    document.getElementById('paymentTotal').value = '$' + parseFloat(this.getAttribute('data-total')).toFixed(2);
                    paymentModal.show();
                });
            });

            document.querySelectorAll('.comment-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('commentOrderId').value = this.getAttribute('data-order-id');
                    commentModal.show();
                });
            });
        });
    </script>
</body>

</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
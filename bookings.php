<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$link = getDB();

// Vérifier si les variables de session existent
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Detect if the user is an administrator
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Initialize messages
$delete_message = '';
$success_message = '';
$error_message = '';

// Delete booking functionality
if (isset($_GET["delete"]) && !empty($_GET["delete"])) {
    $delete_sql = "DELETE FROM Bookings WHERE booking_id = ?";

    if ($is_admin) {
        $delete_sql = "DELETE FROM Bookings WHERE booking_id = ?";
    }

    if ($delete_stmt = mysqli_prepare($link, $delete_sql)) {
        mysqli_stmt_bind_param($delete_stmt, "i", $param_id);
        $param_id = $_GET["delete"];

        if (mysqli_stmt_execute($delete_stmt)) {
            $_SESSION['message'] = "Booking successfully deleted.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['message'] = "Oops! Something went wrong. Please try again later.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        mysqli_stmt_close($delete_stmt);
    }
}

// Update or Add booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['booking_id']) && !empty($_POST['booking_id'])) {
        // Update an existing booking (admin only)
        if ($is_admin) {
            $update_sql = "UPDATE Bookings SET booking_time = ?, status = ? WHERE booking_id = ?";
            if ($update_stmt = mysqli_prepare($link, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "ssi", $param_booking_time, $param_status, $param_booking_id);
                $param_booking_time = $_POST['booking_time'];
                $param_status = $_POST['status'];
                $param_booking_id = $_POST['booking_id'];

                if (mysqli_stmt_execute($update_stmt)) {
                    $_SESSION['message'] = "Booking successfully updated.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $_SESSION['message'] = "Error updating booking.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
                mysqli_stmt_close($update_stmt);
            }
        }
    } else {
        // Add a new booking
        $sql = "INSERT INTO Bookings (user_id, booking_time, status) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iss", $param_user_id, $param_booking_time, $param_status);

            $param_user_id = $user_id;
            $param_booking_time = $_POST['booking_time'];
            $param_status = $_POST['status'];

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Booking successfully saved.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $_SESSION['message'] = "Error saving booking.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch bookings based on Booking ID search (admin only)
if ($is_admin && isset($_GET['search']) && !empty($_GET['search'])) {
    $search_id = $_GET['search'];
    $fetch_sql = "SELECT b.booking_id, b.user_id, b.booking_time, b.status, u.username FROM Bookings b JOIN Users u ON b.user_id = u.user_id WHERE b.booking_id = ?";
    if ($fetch_stmt = mysqli_prepare($link, $fetch_sql)) {
        mysqli_stmt_bind_param($fetch_stmt, "i", $search_id);
        mysqli_stmt_execute($fetch_stmt);
        $result = mysqli_stmt_get_result($fetch_stmt);
    }
} else {
    // Regular fetch for all bookings (admin or user)
    if ($is_admin) {
        $fetch_sql = "SELECT b.booking_id, b.user_id, b.booking_time, b.status, u.username FROM Bookings b JOIN Users u ON b.user_id = u.user_id";
    } else {
        $fetch_sql = "SELECT booking_id, booking_time, status FROM Bookings WHERE user_id = ?";
    }

    if ($fetch_stmt = mysqli_prepare($link, $fetch_sql)) {
        if (!$is_admin) {
            mysqli_stmt_bind_param($fetch_stmt, "i", $user_id);
        }

        mysqli_stmt_execute($fetch_stmt);
        $result = mysqli_stmt_get_result($fetch_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center text-primary">Manage Bookings</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Search Form (Admin Only) -->
        <?php if ($is_admin): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title text-secondary">Search Booking by ID</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3">
                        <div class="col-md-8">
                            <input type="number" name="search" class="form-control" placeholder="Enter Booking ID" required>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title text-secondary">Add or Edit Booking</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="row g-3">
                    <input type="hidden" name="booking_id" id="booking_id">
                    <div class="col-md-6">
                        <label for="booking_time" class="form-label">Booking Time</label>
                        <input type="datetime-local" name="booking_time" id="booking_time" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Save Booking</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-secondary">Bookings List</h2>
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking ID</th>
                            <?php if ($is_admin): ?>
                                <th>User ID</th>
                                <th>Username</th>
                            <?php endif; ?>
                            <th>Booking Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                                    <?php if ($is_admin): ?>
                                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <?php if ($is_admin): ?>
                                            <a href="#" class="btn btn-warning btn-sm edit-booking" data-id="<?php echo $row['booking_id']; ?>" data-time="<?php echo $row['booking_time']; ?>" data-status="<?php echo $row['status']; ?>">Edit</a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $row['booking_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable editing for admins
        document.querySelectorAll('.edit-booking').forEach(button => {
            button.addEventListener('click', () => {
                const bookingId = button.getAttribute('data-id');
                const bookingTime = button.getAttribute('data-time');
                const status = button.getAttribute('data-status');

                document.getElementById('booking_id').value = bookingId;
                document.getElementById('booking_time').value = bookingTime;
                document.getElementById('status').value = status;
            });
        });
    </script>
</body>
</html>

<?php
mysqli_close($link);
?>

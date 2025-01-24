<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$message = "";
$link = getDB(); // Database connection

// Fetch user details
$sql = "SELECT username, email FROM Users WHERE user_id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION["user_id"]);
    if ($stmt->execute()) {
        $stmt->bind_result($username, $email);
        $stmt->fetch();
    }
    $stmt->close();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = trim($_POST["username"]);
    $new_email = trim($_POST["email"]);
    $new_password = trim($_POST["password"]);

    // Update logic
    $update_sql = "UPDATE Users SET username = ?, email = ?, password = ? WHERE user_id = ?";
    if ($update_stmt = $link->prepare($update_sql)) {
        // Hash password if provided
        $hashed_password = $new_password ? password_hash($new_password, PASSWORD_DEFAULT) : null;

        $update_stmt->bind_param(
            "sssi",
            $new_username,
            $new_email,
            $hashed_password,
            $_SESSION["user_id"]
        );

        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $username = $new_username;
            $email = $new_email;
        } else {
            $message = "Error updating profile: " . htmlspecialchars($link->error);
        }
        $update_stmt->close();
    }
}

$link->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center text-primary">Update Your Profile</h1>

        <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Leave blank to keep current password">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="customer_dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

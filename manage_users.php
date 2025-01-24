<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Get database connection
$link = getDB();

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle delete request
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $link->prepare("DELETE FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Handle add/edit user
if (isset($_POST['save_user'])) {
    $user_id = $_POST['user_id'] ?? null; // Determine if it's add or edit
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);

    if ($user_id) {
        // Update existing user
        $stmt = $link->prepare("UPDATE Users SET username = ?, email = ?, role = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    } else {
        // Add new user
        $password = password_hash(sanitize($_POST['password']), PASSWORD_BCRYPT);
        $stmt = $link->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
    }
    $stmt->execute();
    $stmt->close();
}

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to avoid resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all users
$result = $link->query("SELECT * FROM Users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Manage Users</h1>
    <div class="text-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openModal()">Add New User</button>
    </div>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick='openModal(<?= json_encode($row) ?>)'>Edit</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Combined Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add/Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user_id" name="user_id">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3" id="passwordField">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="save_user" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Open modal and populate fields for editing
    function openModal(user = null) {
        const modalTitle = document.getElementById('userModalLabel');
        const userId = document.getElementById('user_id');
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const role = document.getElementById('role');
        const passwordField = document.getElementById('passwordField');

        if (user) {
            modalTitle.textContent = 'Edit User';
            userId.value = user.user_id;
            username.value = user.username;
            email.value = user.email;
            role.value = user.role;
            passwordField.style.display = 'none'; // Hide password field for editing
        } else {
            modalTitle.textContent = 'Add New User';
            userId.value = '';
            username.value = '';
            email.value = '';
            password.value = '';
            role.value = 'customer';
            passwordField.style.display = 'block'; // Show password field for adding
        }
    }
</script>
</body>
</html>

<?php
session_start();
require_once 'config.php'; // Database configuration file

// Initialize variables and error messages
$username = $password = "";
$username_err = $password_err = "";
$login_success_message = ""; // For optional success feedback

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate username
    $username = trim($_POST["username"] ?? "");
    if (empty($username)) {
        $username_err = "Please enter your username.";
    }

    // Validate password
    $password = trim($_POST["password"] ?? "");
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    // Proceed if no errors
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
        $link = getDB(); // Get database connection

        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("s", $username);

            // Execute query
            if ($stmt->execute()) {
                $stmt->store_result();

                // Verify username exists and fetch result
                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($user_id, $db_username, $hashed_password, $role);
                    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
                        // Valid login, initialize session variables
                        session_regenerate_id(true);
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["username"] = $db_username;
                        $_SESSION["role"] = $role;

                        // Optional success feedback before redirecting
                        $login_success_message = "Login successful! Redirecting...";

                        // Redirect to authentication page after a short delay
                        echo "<script>
                                setTimeout(function(){
                                    window.location.href = 'authentication.php';
                                }, 2000);
                              </script>";
                    } else {
                        // Incorrect password
                        $password_err = "The password you entered is incorrect.";
                    }
                } else {
                    // Username not found
                    $username_err = "No account found with that username.";
                }
            } else {
                error_log("Database query error: " . $stmt->error); // Log errors without exposing them
                die("Oops! Something went wrong. Please try again later.");
            }
            $stmt->close();
        }
        $link->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            background-image: url('https://lafrodisiacrestaurant.fr/wp-content/uploads/2021/02/LAdrodisiac-Restaurant-Photo-3-2048x1365.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center center;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .is-invalid+.help-block {
            color: red;
            font-size: 0.9em;
        }

        .password-container {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 35px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2 class="text-center">Login</h2>
        <p class="text-center">Please enter your credentials to login.</p>

        <!-- Optional success message if login is correct -->
        <?php if (!empty($login_success_message)): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($login_success_message); ?></div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
            <!-- Username -->
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username"
                    class="form-control <?= !empty($username_err) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($username); ?>" placeholder="Enter your username" required />
                <div class="help-block"><?= $username_err; ?></div>
            </div>

            <!-- Password -->
            <div class="mb-3 password-container">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password"
                    class="form-control <?= !empty($password_err) ? 'is-invalid' : ''; ?>"
                    placeholder="Enter your password" required />
                <span class="eye-icon" id="toggle-password">🙈</span>
                <div class="help-block"><?= $password_err; ?></div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <p class="text-center mt-3">
            Don't have an account? <a href="signup.php">Sign up now</a>.
        </p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to toggle password visibility and eye icon
        const passwordField = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('toggle-password');

        togglePasswordIcon.addEventListener('click', function () {
            if (passwordField.type === 'password') {
                // Show password
                passwordField.type = 'text';
                togglePasswordIcon.innerHTML = '👁️';
            } else {
                // Hide password
                passwordField.type = 'password';
                togglePasswordIcon.innerHTML = '🙈';
            }
        });
    </script>
</body>

</html>
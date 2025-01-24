<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", trim($_POST["username"]))) {
        $username_err = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
    } else {
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = getDB()->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $username_err = "Error checking username. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = getDB()->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email address is already in use.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                $email_err = "Error checking email. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST['password']))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST['password']);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password !== $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        if ($stmt = getDB()->prepare($sql)) {
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute()) {
                header("location: login.php");
                exit;
            } else {
                echo "<div class='alert alert-danger text-center'>Something went wrong. Please try again later.</div>";
            }
            $stmt->close();
        }
    }
    getDB()->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .signup-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            padding: 20px;
            background-color: #343a40;
            color: white;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .form-label::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header>
        <h1>Create Your Account</h1>
        <p>Join us for exclusive benefits!</p>
    </header>

    <!-- Sign Up Form Section -->
    <div class="signup-container">
        <h2 class="text-center">Sign Up</h2>
        <p class="text-center">Please fill in this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- Username Input -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username"
                    class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo htmlspecialchars($username); ?>" aria-required="true" autocomplete="username">
                <div class="invalid-feedback"> <?php echo $username_err; ?> </div>
                <small class="form-text text-muted">3-20 characters, letters, numbers, underscores only.</small>
            </div>

            <!-- Email Input -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email"
                    class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo htmlspecialchars($email); ?>" aria-required="true" autocomplete="email">
                <div class="invalid-feedback"> <?php echo $email_err; ?> </div>
            </div>

            <!-- Password Input -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password"
                    class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required
                    autocomplete="new-password">
                <div class="invalid-feedback"> <?php echo $password_err; ?> </div>
                <small class="form-text text-muted">Must be at least 6 characters.</small>
            </div>

            <!-- Confirm Password Input -->
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password"
                    class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                <div class="invalid-feedback"> <?php echo $confirm_password_err; ?> </div>
            </div>

            <!-- Submit and Reset Buttons -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100">Submit</button>
                <button type="reset" class="btn btn-secondary w-100 mt-2">Reset</button>
            </div>
            <p class="text-center">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>

    <script>
        // Provide real-time feedback for username
        document.getElementById('username').addEventListener('input', function () {
            const feedback = document.querySelector('.invalid-feedback');
            if (this.value.length < 3 || this.value.length > 20) {
                feedback.textContent = 'Username must be 3-20 characters long.';
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

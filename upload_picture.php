<?php
// Enable error reporting for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database configuration file
require_once 'config.php';

// Initialize variables
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a file is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Allowed file extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Set the upload directory
            $uploadFileDir = 'uploads/profile_pictures/';
            // Create directory if it doesn't exist
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            // Generate a unique name for the file
            $newFileName = $_SESSION['username'] . '_' . time() . '.' . $fileExtension;
            $destPath = $uploadFileDir . $newFileName;

            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Update the profile picture path in the database
                $username = $_SESSION['username'];
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $destPath, $username);

                    if ($stmt->execute()) {
                        $message = 'Profile picture uploaded successfully!';
                    } else {
                        $message = 'Failed to update profile picture in the database.';
                    }
                    $stmt->close();
                } else {
                    $message = 'Database error: ' . $conn->error;
                }
            } else {
                $message = 'Failed to move the uploaded file.';
            }
        } else {
            $message = 'Invalid file extension. Allowed: ' . implode(", ", $allowedExtensions);
        }
    } else {
        $message = 'Please upload a file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Upload Profile Picture</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="upload_picture.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Choose a profile picture</label>
                <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>

</html>

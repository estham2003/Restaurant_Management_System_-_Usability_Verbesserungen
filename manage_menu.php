<?php
session_start();
require_once 'config.php';

$link = getDB();

// Check if the user is logged in and is an admin or staff
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('location: login.php');
    exit;
}

// Handling POST request for adding/updating menu items
function handlePostRequest($link) {
    if (isset($_POST['name'], $_POST['description'], $_POST['price'], $_POST['category_id'])) {
        $menuId = isset($_POST['menu_id']) ? $_POST['menu_id'] : NULL;

        // Handle image upload
        $imageUrl = '';
        if (isset($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["image"]["name"]);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES["image"]["type"], $allowedTypes) && move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }

        $sql = $menuId ? "UPDATE Menu SET name = ?, description = ?, price = ?, category_id = ?, image_url = ? WHERE menu_id = ?"
                       : "INSERT INTO Menu (name, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            $menuId ? mysqli_stmt_bind_param($stmt, 'ssdiss', $_POST['name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $imageUrl, $menuId)
                    : mysqli_stmt_bind_param($stmt, 'ssdss', $_POST['name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $imageUrl);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Handling POST request for adding categories
function handleCategoryPost($link) {
    if (isset($_POST['category_name'])) {
        $sql = "INSERT INTO Categories (name) VALUES (?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $_POST['category_name']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Handling delete requests for items and categories
function handleDeleteRequest($link, $type, $id) {
    $sql = ($type === 'item') ? "DELETE FROM Menu WHERE menu_id = ?" : "DELETE FROM Categories WHERE category_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Fetch categories to use in the dropdown and tables
$categories = [];
$categoriesResult = mysqli_query($link, "SELECT category_id, name FROM Categories");
if ($categoriesResult) {
    while ($category = mysqli_fetch_assoc($categoriesResult)) {
        $categories[$category['category_id']] = $category['name'];
    }
    mysqli_free_result($categoriesResult);
}

// Fetch all menu items for display
$MenuResult = mysqli_query($link, "SELECT menu_id, name, description, price, category_id, image_url FROM Menu");

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest($link);
    handleCategoryPost($link);
    // Redirect to avoid resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Process GET requests for deletions
if (isset($_GET['delete_item'])) {
    handleDeleteRequest($link, 'item', $_GET['delete_item']);
}
if (isset($_GET['delete_category'])) {
    handleDeleteRequest($link, 'category', $_GET['delete_category']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        img { max-width: 100px; height: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f4f4f4; }
        .container { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4 text-center">Manage menus and categories</h1>

        <!-- Category Management Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Add new category</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="category_name">Category name:</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>

        <!-- Display Categories -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Current Categories</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $id => $name): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($id); ?></td>
                                <td><?php echo htmlspecialchars($name); ?></td>
                                <td>
                                    <a href="?delete_category=<?php echo $id; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form to add or update menu items -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Add or Update Menu Items</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="menu_id" value="<?php echo isset($_GET['edit']) ? $_GET['edit'] : ''; ?>">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="text" name="image" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Save Item</button>
                </form>
            </div>
        </div>

        <!-- Display Menu Items -->
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Menu Items</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($MenuResult): ?>
                        <?php while ($item = mysqli_fetch_assoc($MenuResult)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Image">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($categories[$item['category_id']]); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $item['menu_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?delete_item=<?php echo $item['menu_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No menu items found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script async src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script async src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script async src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
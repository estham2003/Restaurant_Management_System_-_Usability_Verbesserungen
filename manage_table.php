<?php
declare(strict_types=1);
session_start();
require_once 'config.php';

// Ensure admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Get database connection
$link = getDB();

// Sanitize and validate input
function sanitize(string $data): string
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Reusable function for database queries
function executeQuery(mysqli $link, string $query, string $types, ...$params): bool
{
    $stmt = $link->prepare($query);
    if (!$stmt) {
        error_log("Database error: " . $link->error);
        return false;
    }
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tableId = intval($_POST['table_id'] ?? 0);
    $tableNr = intval($_POST['table_nr'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    if ($action === 'create_table' && $tableNr && $status) {
        $query = "INSERT INTO tables (table_nr, status) VALUES (?, ?)";
        executeQuery($link, $query, 'is', $tableNr, $status);
    } elseif ($action === 'update_table' && $tableId && $tableNr && $status) {
        $query = "UPDATE tables SET table_nr = ?, status = ? WHERE table_id = ?";
        executeQuery($link, $query, 'isi', $tableNr, $status, $tableId);
    } elseif ($action === 'delete_table' && $tableId) {
        $query = "DELETE FROM tables WHERE table_id = ?";
        executeQuery($link, $query, 'i', $tableId);
    }
    // Redirect to avoid resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all tables
$result = $link->query("SELECT * FROM tables");
$tables = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Manage Tables</h1>
        <div class="text-end mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tableModal" onclick="showModal('create')">Add New Table</button>
        </div>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Table Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                    <tr>
                        <td><?= htmlspecialchars($table['table_nr']) ?></td>
                        <td><?= htmlspecialchars($table['status']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#tableModal"
                                onclick="showModal('edit', <?= htmlspecialchars(json_encode($table)) ?>)">Edit</button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="table_id" value="<?= $table['table_id'] ?>">
                                <input type="hidden" name="action" value="delete_table">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Table Modal -->
    <div class="modal fade" id="tableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Table</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="create_table">
                    <input type="hidden" name="table_id" id="modalTableId">
                    <div class="mb-3">
                        <label for="table_nr" class="form-label">Table Number</label>
                        <input type="number" name="table_nr" id="modalTableNr" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="modalStatus" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="not">Not</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(action, table = {}) {
            const modalTitle = document.getElementById('modalTitle');
            const modalAction = document.getElementById('modalAction');
            const modalTableId = document.getElementById('modalTableId');
            const modalTableNr = document.getElementById('modalTableNr');
            const modalStatus = document.getElementById('modalStatus');

            if (action === 'edit') {
                modalTitle.textContent = 'Edit Table';
                modalAction.value = 'update_table';
                modalTableId.value = table.table_id || '';
                modalTableNr.value = table.table_nr || '';
                modalStatus.value = table.status || 'available';
            } else {
                modalTitle.textContent = 'Add New Table';
                modalAction.value = 'create_table';
                modalTableId.value = '';
                modalTableNr.value = '';
                modalStatus.value = 'available';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

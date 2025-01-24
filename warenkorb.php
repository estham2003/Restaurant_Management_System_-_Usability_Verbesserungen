<?php
session_start();
require_once 'config.php';

// Fonction pour nettoyer les entrées utilisateur
function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

// Vérifier si les données du panier existent
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order'])) {
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $tableId = filter_input(INPUT_POST, 'table_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if (!$itemId || !$tableId || !$quantity || $quantity <= 0 || !$price) {
        die("Invalid order details. Please check your input.");
    }

    // Calcul du total
    $total = $quantity * $price;

    // Ajouter au panier (session)
    $_SESSION['cart'][] = [
        'item_id' => $itemId,
        'table_id' => $tableId,
        'quantity' => $quantity,
        'price' => $price,
        'total' => $total
    ];
}

// Gérer la suppression d'un élément du panier
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_item'])) {
    $index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Réindexer le tableau
    }
}

// Calculer le total général du panier
function calculateCartTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['total'];
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warenkorb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-dark text-white p-3 text-center">
        <h1>Warenkorb</h1>
    </header>
    <main class="container my-4">
        <h2>Inhalt Ihres Warenkorbs</h2>

        <?php if (!empty($_SESSION['cart'])): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produkt ID</th>
                        <th>Table No</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= sanitizeInput($item['item_id']) ?></td>
                            <td><?= sanitizeInput($item['table_id']) ?></td>
                            <td><?= sanitizeInput($item['quantity']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>$<?= number_format($item['total'], 2) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="index" value="<?= $index ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total Général</th>
                        <th>$<?= number_format(calculateCartTotal($_SESSION['cart']), 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Formulaire de paiement -->
            <form method="POST" action="order_history.php">
                <div class="mb-3">
                    <label>Méthode de paiement :</label>
                    <div>
                        <input type="radio" name="payment_type" value="cash" required> Cash
                        <input type="radio" name="payment_type" value="online" required> Online
                    </div>
                </div>
                <button type="submit" name="checkout" class="btn btn-success w-100">Payer</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning text-center">Votre panier est vide.</div>
        <?php endif; ?>
    </main>
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p>&copy; <?= date('Y') ?> Afromix Restaurant. Tous droits réservés.</p>
    </footer>
</body>
</html>

<?php
// Sessie starten
session_start();

// Limieten instellen
define('MAX_CART_ITEMS', 5000); // Maximum totaal aantal items in winkelwagen
define('MAX_PRODUCT_QUANTITY', 100); // Maximum aantal van hetzelfde product

// Winkelwagen aanmaken als die er niet is
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = array();
}

// Product toevoegen
if (isset($_POST["product"])) {
    $product_id = (int)$_POST["product"];
    
    // Check totaal aantal items in winkelwagen
    if (count($_SESSION["cart"]) < MAX_CART_ITEMS) {
        // Check aantal van dit specifieke product
        $current_count = array_count_values($_SESSION["cart"]);
        $product_count = isset($current_count[$product_id]) ? $current_count[$product_id] : 0;
        
        if ($product_count < MAX_PRODUCT_QUANTITY) {
            $_SESSION["cart"][] = $product_id;
            $success_message = "Product toegevoegd aan winkelwagen!";
        } else {
            $error_message = "Maximum aantal (" . MAX_PRODUCT_QUANTITY . ") van dit product bereikt!";
        }
    } else {
        $error_message = "Winkelwagen vol! Maximum " . MAX_CART_ITEMS . " items toegestaan.";
    }
}
// Product verwijderen
if (isset($_POST["remove_from_cart"]) && isset($_POST["remove_id"])) {
    $remove_id = (int)$_POST["remove_id"];
    // Alle instanties van dit product verwijderen uit de cart
    $_SESSION["cart"] = array_filter($_SESSION["cart"], function($id) use ($remove_id) {
        return $id != $remove_id;
    });
    // Array opnieuw indexeren
    $_SESSION["cart"] = array_values($_SESSION["cart"]);
}
// Aantal aanpassen in winkelwagen
if (isset($_POST['update_quantity'])) {
    $update_id = (int)$_POST['update_id'];
    $aantal = max(1, min(MAX_PRODUCT_QUANTITY, (int)$_POST['quantity'])); // Limiet toepassen
    
    // Check of nieuwe aantal past binnen totaal limiet
    $current_total = count($_SESSION['cart']);
    $current_product_count = count(array_filter($_SESSION['cart'], function($id) use ($update_id) { 
        return $id == $update_id; 
    }));
    $new_total = $current_total - $current_product_count + $aantal;
    
    if ($new_total <= MAX_CART_ITEMS) {
        // Oude entries verwijderen
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($id) use ($update_id) { 
            return $id != $update_id; 
        });
        // Juiste aantal toevoegen
        for ($i = 0; $i < $aantal; $i++) {
            $_SESSION['cart'][] = $update_id;
        }
        // Array opnieuw indexeren
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $success_message = "Aantal aangepast!";
    } else {
        $error_message = "Kan aantal niet aanpassen: zou totaal limiet van " . MAX_CART_ITEMS . " items overschrijden!";
    }
}

// Aantal per product tellen
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart_aantallen = array_count_values($cart);

$conn = new mysqli("localhost", "root", "root", "eindproduct");
if ($conn->connect_error) {
    die("Databaseverbinding mislukt: " . $conn->connect_error);
}
$games = [];
$totaal = 0;
if (!empty($cart_aantallen)) {
    $ids = implode(',', array_map('intval', array_keys($cart_aantallen)));
    $result = $conn->query("SELECT ID, naam, game, prijs, afbeelding FROM producten WHERE ID IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $row['aantal'] = $cart_aantallen[$row['ID']];
        $games[] = $row;
        $totaal += $row['prijs'] * $row['aantal'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="toets.css">
    <link rel="stylesheet" href="custom.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Jouw Winkelwagen</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <small class="text-muted">
            Limiet: Maximum <?= MAX_CART_ITEMS ?> items totaal, <?= MAX_PRODUCT_QUANTITY ?> per product
        </small>
    </div>
    
    <?php if (empty($games)): ?>
        <div class="alert alert-info">Je winkelwagen is leeg.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($games as $game): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="game-image">
                            <?php if (!empty($game['afbeelding'])): ?>
                                <img src="fotos/<?= htmlspecialchars($game['afbeelding']) ?>" alt="<?= htmlspecialchars($game['game']) ?>" class="img-fluid w-100 h-100" style="object-fit:cover;">
                            <?php else: ?>
                                <span class="text-muted"><?= htmlspecialchars($game['game']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($game['game']) ?></h5>
                            <p class="card-text text-muted small">Product: <?= htmlspecialchars($game['naam']) ?></p>
                            <h6 class="text-primary">€<?= number_format($game['prijs'], 2, ',', '.') ?></h6>
                            <form method="post" class="d-flex align-items-center mb-2">
                                <input type="hidden" name="update_id" value="<?= $game['ID'] ?>">
                                <input type="number" name="quantity" value="<?= $game['aantal'] ?>" min="1" max="<?= MAX_PRODUCT_QUANTITY ?>" class="form-control me-2" style="width:80px;" title="Maximum <?= MAX_PRODUCT_QUANTITY ?> stuks">
                                <button type="submit" name="update_quantity" class="btn btn-sm btn-primary">Aantal aanpassen</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="remove_id" value="<?= $game['ID'] ?>">
                                <button type="submit" name="remove_from_cart" class="btn btn-sm btn-danger">Verwijder uit winkelwagen</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 text-end">
            <h4>Totaal: €<?= number_format($totaal, 2, ',', '.') ?></h4>
        </div>
    <?php endif; ?>
    <div class="mt-4">
        <a href="index.php" class="btn btn-outline-secondary">Verder winkelen</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

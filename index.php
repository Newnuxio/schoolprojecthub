<?php
try {
    session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

    $conn = new mysqli("localhost", "root", "root", "eindproduct");
    if ($conn->connect_error) {
        throw new Exception("Verbinding mislukt: " . $conn->connect_error);
    }
    $loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];
    echo "<!DOCTYPE html>
    <html lang='nl'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>GameStop Beheer</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='toets.css'>
        <link rel='stylesheet' href='index.css'>
    </head>
    <body>";

    // Account/Login knop
    if ($loggedin) {
        echo "<a href='account.php' class='btn btn-primary account-btn'>
                <i class='fas fa-user me-1'></i> Mijn Account
              </a>";
    }

    // Klant verwijderen
    if (isset($_GET['delete_customer'])) {
        $id = (int)$_GET['delete_customer'];
        $stmt = $conn->prepare("DELETE FROM klanten WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Klant aanmaken
    if (isset($_POST['create_customer'])) {
        $naam = htmlspecialchars($_POST['naam'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $wachtwoord = $_POST['wachtwoord'];
        $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO klanten (naam, email, wachtwoord) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $naam, $email, $hash);
        if ($stmt->execute()) {
            echo "<div class='container mt-3'>
                    <div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Nieuwe klant succesvol aangemaakt!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>
                  </div>";
        }
        $stmt->close();
    }

    // Formulier voor klant aanmaken tonen
    if (isset($_GET['create_customer'])) {
        echo "<div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-6 col-lg-4'>
                    <div class='account-form-container card shadow-lg border-0'>
                        <div class='card-body'>
                            <h2 class='text-center mb-4'>Account Maken</h2>
                            <form method='post' action=''>
                                <div class='form-group mb-3'>
                                    <input type='text' name='naam' class='form-control' placeholder='Voor+ Achternaam' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <input type='email' name='email' class='form-control' placeholder='Email' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <input type='password' name='wachtwoord' class='form-control' placeholder='Wachtwoord' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <input type='password' name='herhaal_wachtwoord' class='form-control' placeholder='Herhaal Wachtwoord' required>
                                </div>
                                <div class='d-grid'>
                                    <button type='submit' name='create_customer' class='submit-btn btn btn-success btn-lg'>Account Maken</button>
                                </div>
                                <div class='text-center mt-3'>
                                    <a href='index.php' class='btn btn-outline-secondary'>Terug</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    } 
    // Hoofd-dashboard tonen
    else {
        echo "<div class='container mt-4'>
                <div class='row'>
                    <div class='col-12'>
                        <h1 class='mb-4 text-center'>GameStop</h1>
                        <div class='mb-4 text-center'>";
        if (!$loggedin) {
            echo "<a href='login.php' class='btn btn-outline-secondary me-2'>Inloggen</a>";
        }
        echo    "<a href='create_account.php?create_customer=1' class='btn btn-success me-2'>
                + Nieuwe Klant
            </a>
                        </div>";

        // Games sectie
        echo "<div class='row mb-5'>
                <div class='col-12'>
                    <h2 class='mb-4'>Games Collectie</h2>";
        $games_stmt = $conn->prepare("SELECT ID, naam, game, prijs, stock, uitgever, genre, age_rating, afbeelding FROM producten ORDER BY game");
        $games_stmt->execute();
        $games_result = $games_stmt->get_result();
        if ($games_result->num_rows > 0) {
            echo "<div class='row'>";
            while ($product = $games_result->fetch_assoc()) {
                $stock_class = $product['stock'] > 5 ? 'bg-success' : ($product['stock'] > 0 ? 'bg-warning' : 'bg-danger');
                $stock_text = $product['stock'] > 0 ? $product['stock'] . ' op voorraad' : 'Uitverkocht';
                echo "<div class='col-md-6 col-lg-4 col-xl-3 mb-4'>
                        <div class='card game-card h-100 shadow-sm'>
                            <div class='position-relative'>
                                <div class='game-image'>";
                if (!empty($product['afbeelding'])) {
                    echo "<img src='fotos/" . $product['afbeelding'] . "' alt='" . htmlspecialchars($product['game']) . "' class='img-fluid w-100 h-100' style='object-fit:cover;'>";
                } else {
                    echo "<span class='text-muted'>" . htmlspecialchars($product['game']) . "</span>";
                }
                echo    "</div>
                    <span class='badge {$stock_class} stock-badge'>{$stock_text}</span>
                    <span class='badge bg-dark age-rating-badge'>PEGI " . htmlspecialchars($product['age_rating']) . "</span>
                </div>
                <div class='card-body d-flex flex-column'>
                    <h5 class='card-title'>" . htmlspecialchars($product['game']) . "</h5>
                    <p class='card-text text-muted small'>" . htmlspecialchars($product['naam']) . "</p>
                    <p class='card-text small'><strong>Genre:</strong> " . htmlspecialchars($product['genre']) . "</p>
                    <p class='card-text small'><strong>Uitgever:</strong> " . htmlspecialchars($product['uitgever']) . "</p>
                <div class='mt-auto'>
                        <h4 class='text-primary mb-0'>€" . number_format($product['prijs'], 2, ',', '.') . "</h4>";
                if ($loggedin) {
                    echo "<form method='post' class='mt-2'>
                            <input type='hidden' name='game_id' value='" . $product['ID'] . "'>
                            <button type='submit' name='add_to_cart' class='btn btn-success w-100'>Toevoegen aan winkelwagen</button>
                          </form>";
                }
                echo    "</div>
                </div>
            </div>
          </div>";
            }
            echo "</div>";
        } else {
            echo "<div class='text-center py-5'>
                    <p class='text-muted'>Nog geen games toegevoegd.</p>
                  </div>";
        }
        $games_stmt->close();
        echo "</div></div>";
    }

    // Winkelwagen functionaliteit
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Toevoegen aan winkelwagen
    if (isset($_POST['add_to_cart']) && $loggedin) {
        $game_id = (int)$_POST['game_id'];
        if (!in_array($game_id, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $game_id;
        }
    }
    // Winkelwagen tonen als je bent ingelogd
    if ($loggedin) {
        echo "<a href='winkelwagen.php' class='btn btn-warning position-fixed' style='top:80px;right:20px;z-index:1001;'>Winkelwagen (" . count($_SESSION['cart']) . ")</a>";
    }

    $conn->close();
    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body></html>";

} catch (Exception $e) {
    echo "<div class='container mt-4'>
            <div class='alert alert-danger' role='alert'>
                Er is een fout opgetreden: " . htmlspecialchars($e->getMessage()) . "
            </div>
          </div>";
}
?>
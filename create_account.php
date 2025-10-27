<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $conn = new mysqli("localhost", "root", "root", "eindproduct");
    if ($conn->connect_error) {
        throw new Exception("Verbinding mislukt: " . $conn->connect_error);
    }
    echo "<!DOCTYPE html>
    <html lang='nl'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>GameStop Beheer</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='toets.css'>
        <style>
            .game-card {
                transition: transform 0.2s;
                cursor: pointer;
            }
            .game-card:hover {
                transform: translateY(-5px);
            }
            .game-image {
                height: 200px;
                object-fit: cover;
                background: linear-gradient(45deg, #f8f9fa, #e9ecef);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .stock-badge {
                position: absolute;
                top: 10px;
                left: 10px;
                z-index: 10;
            }
            .age-rating-badge {
                position: absolute;
                bottom: 10px;
                right: 10px;
                z-index: 10;
            }
        </style>
    </head>
    <body>";

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
            header('Location: index.php');
            exit;
        }
        $stmt->close();
    }

    // Game aanmaken
    if (isset($_POST['create_game'])) {
        $naam = htmlspecialchars($_POST['naam'], ENT_QUOTES, 'UTF-8');
        $game = htmlspecialchars($_POST['game'], ENT_QUOTES, 'UTF-8');
        $prijs = (float)$_POST['prijs'];
        $stock = (int)$_POST['stock'];
        $uitgever = htmlspecialchars($_POST['uitgever'], ENT_QUOTES, 'UTF-8');
        $genre = htmlspecialchars($_POST['genre'], ENT_QUOTES, 'UTF-8');
        $age_rating = htmlspecialchars($_POST['age_rating'], ENT_QUOTES, 'UTF-8');
        
        $stmt = $conn->prepare("INSERT INTO producten (naam, game, prijs, stock, uitgever, genre, age_rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiss", $naam, $game, $prijs, $stock, $uitgever, $genre, $age_rating);
        if ($stmt->execute()) {
            echo "<div class='container mt-3'>
                    <div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Nieuwe game succesvol toegevoegd!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>
                  </div>";
        }
        $stmt->close();
    }

    // Toon formulier voor het aanmaken van een klantaccount
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
    // Toon formulier voor het aanmaken van een game
    elseif (isset($_GET['create_game'])) {
        echo "<div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-8 col-lg-6'>
                    <div class='card shadow-lg border-0'>
                        <div class='card-body'>
                            <h2 class='text-center mb-4'>Game Toevoegen</h2>
                            <form method='post' action=''>
                                <div class='form-group mb-3'>
                                    <label for='naam' class='form-label'>Product Naam</label>
                                    <input type='text' name='naam' id='naam' class='form-control' placeholder='bijv. Call of Duty Black Ops 6 PS5' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='game' class='form-label'>Game Titel</label>
                                    <input type='text' name='game' id='game' class='form-control' placeholder='bijv. Call of Duty: Black Ops 6' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='prijs' class='form-label'>Prijs (€)</label>
                                    <input type='number' name='prijs' id='prijs' class='form-control' step='0.01' placeholder='69.99' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='stock' class='form-label'>Voorraad</label>
                                    <input type='number' name='stock' id='stock' class='form-control' placeholder='10' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='uitgever' class='form-label'>Uitgever</label>
                                    <input type='text' name='uitgever' id='uitgever' class='form-control' placeholder='bijv. Activision' required>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='genre' class='form-label'>Genre</label>
                                    <select name='genre' id='genre' class='form-control' required>
                                        <option value=''>Selecteer genre</option>
                                        <option value='Action'>Action</option>
                                        <option value='Adventure'>Adventure</option>
                                        <option value='RPG'>RPG</option>
                                        <option value='Sports'>Sports</option>
                                        <option value='Racing'>Racing</option>
                                        <option value='Strategy'>Strategy</option>
                                        <option value='Simulation'>Simulation</option>
                                        <option value='Puzzle'>Puzzle</option>
                                        <option value='Fighting'>Fighting</option>
                                        <option value='Horror'>Horror</option>
                                    </select>
                                </div>
                                <div class='form-group mb-3'>
                                    <label for='age_rating' class='form-label'>Leeftijdsclassificatie</label>
                                    <select name='age_rating' id='age_rating' class='form-control' required>
                                        <option value=''>Selecteer rating</option>
                                        <option value='3+'>PEGI 3+</option>
                                        <option value='7+'>PEGI 7+</option>
                                        <option value='12+'>PEGI 12+</option>
                                        <option value='16+'>PEGI 16+</option>
                                        <option value='18+'>PEGI 18+</option>
                                    </select>
                                </div>
                                <div class='d-grid'>
                                    <button type='submit' name='create_game' class='btn btn-primary btn-lg'>Game Toevoegen</button>
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
    // Toon hoofd dashboard
    else {
        echo "<div class='container mt-4'>
                <div class='row'>
                    <div class='col-12'>
                        <h1 class='mb-4 text-center'>GameStop Management</h1>
                        <div class='mb-4 text-center'>
                            <a href='login.php' class='btn btn-outline-secondary me-2'>Inloggen</a>
                            <a href='create_account.php?create_customer=1' class='btn btn-success me-2'>
                                + Nieuwe Klant
                            </a>
                        </div>";

        // Games sectie
        echo "<div class='row mb-5'>
                <div class='col-12'>
                    <h2 class='mb-4'>Games Collectie</h2>";
        $games_stmt = $conn->prepare("SELECT ID, naam, game, prijs, stock, uitgever, genre, age_rating FROM producten ORDER BY game");
        $games_stmt->execute();
        $games_result = $games_stmt->get_result();
        if ($games_result->num_rows > 0) {
            echo "<div class='row'>";
            while ($product = $games_result->fetch_assoc()) {
                $stock_class = $product['stock'] > 5 ? 'bg-success' : ($product['stock'] > 0 ? 'bg-warning' : 'bg-danger');
                $stock_text = $product['stock'] > 0 ? $product['stock'] . ' op voorraad' : 'Uitverkocht';
                echo "<div class='col-md-6 col-lg-4 col-xl-3 mb-4'>
                        <a href='showcase.php?id=" . $product['ID'] . "' style='text-decoration:none;color:inherit;'>
                        <div class='card game-card h-100 shadow-sm'>
                            <div class='position-relative'>
                                <div class='game-image'>
                                    <span class='text-muted'>" . htmlspecialchars($product['game']) . "</span>
                                </div>
                                <span class='badge {$stock_class} stock-badge'>{$stock_text}</span>
                                <span class='badge bg-dark age-rating-badge'>PEGI " . htmlspecialchars($product['age_rating']) . "</span>
                            </div>
                            <div class='card-body d-flex flex-column'>
                                <h5 class='card-title'>" . htmlspecialchars($product['game']) . "</h5>
                                <p class='card-text text-muted small'>" . htmlspecialchars($product['naam']) . "</p>
                                <p class='card-text small'><strong>Genre:</strong> " . htmlspecialchars($product['genre']) . "</p>
                                <p class='card-text small'><strong>Uitgever:</strong> " . htmlspecialchars($product['uitgever']) . "</p>
                                <div class='mt-auto'>
                                    <h4 class='text-primary mb-0'>€" . number_format($product['prijs'], 2, ',', '.') . "</h4>
                                </div>
                            </div>
                        </div>
                        </a>
                      </div>";
            }
            echo "</div>";
        } else {
            echo "<div class='text-center py-5'>
                    <p class='text-muted'>Nog geen games toegevoegd.</p>
                    <a href='create_account.php?create_game=1' class='btn btn-primary'>Voeg je eerste game toe</a>
                  </div>";
        }
        $games_stmt->close();
        echo "</div></div>";
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
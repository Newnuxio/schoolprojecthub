<?php
// Sessie starten
session_start();
$error = '';
// Form verstuurd?
if (isset($_POST['login'])) {
    // DB connectie
    $conn = new mysqli("localhost", "root", "root", "eindproduct");
    if ($conn->connect_error) {
        // Geen verbinding
        $error = "Database faalt.";
    } else {
        // Haal naam en wachtwoord
        $naam = $conn->real_escape_string($_POST['naam']);
        $wachtwoord = $conn->real_escape_string($_POST['wachtwoord']);
        // Zoek gebruiker
        $query = "SELECT wachtwoord FROM klanten WHERE naam = '$naam' LIMIT 1";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            // Check wachtwoord
            if (password_verify($wachtwoord, $row['wachtwoord'])) {
                // Sessie en doorsturen
                $_SESSION['loggedin'] = true;
                $_SESSION['naam'] = $naam;
                header('Location: index.php');
                exit;
            } else {
                // Verkeerd wachtwoord
                $error = "Wachtwoord fout.";
            }
        } else {
            // Geen gebruiker
            $error = "Gebruiker niet gevonden.";
        }
        $result && $result->free();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="toets.css">
    <style>
        .account-form-container {
            border-radius: 1rem;
        }
        .submit-btn {
            background: linear-gradient(90deg, #28a745, #218838);
            color: #fff;
        }
        .submit-btn:hover {
            background: linear-gradient(90deg, #218838, #28a745);
        }
    </style>
</head>
<body>
<?php
if (!empty($error)) {
    echo "<div class='container mt-3'>
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>"
            . htmlspecialchars($error) .
            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>
          </div>";
}
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="account-form-container card shadow-lg border-0">
                <div class="card-body">
                    
                    <h2 class="text-center mb-4">Inloggen</h2>
                    <form method="post" action="">
                        <div class="form-group mb-3">
                            <input type="text" name="naam" class="form-control" placeholder="Gebruikersnaam" required>
                        </div>
                        <div class="form-group mb-3">
                            <input type="password" name="wachtwoord" class="form-control" placeholder="Wachtwoord" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="submit-btn btn btn-success btn-lg">Inloggen</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="create_account.php?create_customer=1" class="btn btn-outline-secondary">Account maken</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

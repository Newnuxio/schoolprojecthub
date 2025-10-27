<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $repeat = $_POST['repeat_password'];
    $conn = new mysqli('localhost', 'root', 'root', 'eindproduct');
    if ($conn->connect_error) {
        $_SESSION['error'] = 'Databaseverbinding mislukt.';
        header('Location: account.php');
        exit();
    }
    if ($new !== $repeat) {
        $_SESSION['error'] = 'Nieuwe wachtwoorden komen niet overeen.';
    } else {
        $stmt = $conn->prepare('SELECT wachtwoord FROM klanten WHERE naam = ?');
        $stmt->bind_param('s', $_SESSION['naam']);
        $stmt->execute();
        $stmt->bind_result($hash);
        if ($stmt->fetch() && password_verify($current, $hash)) {
            $stmt->close();
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare('UPDATE klanten SET wachtwoord = ? WHERE naam = ?');
            $stmt2->bind_param('ss', $new_hash, $_SESSION['naam']);
            if ($stmt2->execute()) {
                $_SESSION['success'] = 'Wachtwoord succesvol gewijzigd.';
            } else {
                $_SESSION['error'] = 'Wachtwoord wijzigen mislukt.';
            }
            $stmt2->close();
        } else {
            $_SESSION['error'] = 'Huidig wachtwoord is onjuist.';
        }
        // $stmt->close();
    }
    $conn->close();
}
header('Location: account.php');
exit();

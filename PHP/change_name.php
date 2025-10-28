<?php
session_start();

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable("../");
$dotenv->load();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['change_name'])) {
    $new_naam = htmlspecialchars($_POST['new_naam'], ENT_QUOTES, 'UTF-8');
    $conn = new mysqli('localhost', 'root', 'root', 'eindproduct');
    if ($conn->connect_error) {
        $_SESSION['error'] = 'Databaseverbinding mislukt.';
        header('Location: account.php');
        exit();
    }
    if (!empty($new_naam)) {
        $stmt = $conn->prepare('UPDATE klanten SET naam = ? WHERE naam = ?');
        $stmt->bind_param('ss', $new_naam, $_SESSION['naam']);
        if ($stmt->execute()) {
            $_SESSION['naam'] = $new_naam;
            $_SESSION['success'] = 'Naam succesvol gewijzigd.';
        } else {
            $_SESSION['error'] = 'Naam wijzigen mislukt.';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Vul een nieuwe naam in.';
    }
    $conn->close();
}
header('Location: account.php');
exit();

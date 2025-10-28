<?php
//TODO make better
session_start();

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable("../");
$dotenv->load();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['delete_account'])) {
    $conn = new mysqli('localhost', 'root', 'root', 'eindproduct');
    if ($conn->connect_error) {
        $_SESSION['error'] = 'Databaseverbinding mislukt.';
        header('Location: account.php');
        exit();
    }
    $stmt = $conn->prepare('DELETE FROM klanten WHERE naam = ?');
    $stmt->bind_param('s', $_SESSION['naam']);
    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Account verwijderen mislukt.';
        $stmt->close();
        $conn->close();
        header('Location: account.php');
        exit();
    }
}
header('Location: account.php');
exit();

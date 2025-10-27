<?php
// Sessie starten
session_start();
// Uitloggen
session_destroy();
// Naar login
header("Location: login.php");
exit;
?>

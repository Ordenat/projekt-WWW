<?php
session_start(); // Rozpocznij sesję

// Zniszcz wszystkie dane sesji
$_SESSION = array(); // Ustaw tablicę sesji na pustą

// Zniszcz sesję
session_destroy(); // Zniszcz sesję

// Przekieruj do strony logowania
header('Location: login.php');
exit(); // Zakończ dalsze wykonywanie skryptu
?>

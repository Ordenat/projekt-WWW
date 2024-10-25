<?php
$host = 'localhost';       // Host
$db = 'pracownicy';     // Nazwa bazy danych
$user = 'root';        // Użytkownik
$password = '';    // Hasło

// Tworzenie połączenia
$connection = new mysqli($host, $user, $password, $db);

// Sprawdzenie połączenia
if ($connection->connect_error) {
    die("Połączenie nieudane: " . $connection->connect_error);
}

?>

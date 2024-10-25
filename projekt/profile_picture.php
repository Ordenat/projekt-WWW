<?php

require 'db_connection.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo 'Nie jesteś zalogowany.';
    exit();
}

$user_id = $_SESSION['user_id'];

// Aktualizacja zdjęcia profilowego
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/profile_pictures/';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

        $file_name = basename($_FILES['profile_picture']['name']);
        $file_path = $uploads_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            // Zaktualizuj ścieżkę zdjęcia w bazie danych
            $query = $connection->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $query->bind_param("si", $file_path, $user_id);
            $query->execute();
        }
    }
}

// Pobierz zdjęcie profilowe
$query = $connection->prepare("SELECT profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>
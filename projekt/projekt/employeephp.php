<?php
// Sprawdzenie, czy użytkownik jest zalogowany jako pracownik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit();
}

// Pobranie danych użytkownika i jego zadań oraz raportów
$user_id = $_SESSION['user_id'];

// Pobierz nazwę użytkownika i zdjęcie profilowe
$query = $connection->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$currentUser = $query->get_result()->fetch_assoc();

// Przetwarzanie przesyłania zdjęcia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obsługa przesyłania zdjęcia
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/profile_images/';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $file_path = $uploads_dir . $file_name;

        // Sprawdzenie, czy plik jest obrazem
        $file_type = mime_content_type($_FILES['profile_picture']['tmp_name']);
        if (strpos($file_type, 'image/') === false) {
            // Obsłuż błąd: niepoprawny typ pliku
            echo "Błąd: Niepoprawny typ pliku.";
            exit;
        }

        // Usunięcie starego zdjęcia, jeśli istnieje
        if (!empty($currentUser['profile_picture']) && file_exists($currentUser['profile_picture'])) {
            unlink($currentUser['profile_picture']);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            // Zaktualizuj ścieżkę zdjęcia w bazie danych
            $query = $connection->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $query->bind_param("si", $file_path, $user_id);
            $query->execute();

            // Zaktualizuj informację o użytkowniku
            $currentUser['profile_picture'] = $file_path;
        }
    }

    // Obsługa usunięcia zdjęcia
    if (isset($_POST['delete_picture'])) {
        // Usunięcie starego zdjęcia, jeśli istnieje
        if (!empty($currentUser['profile_picture']) && file_exists($currentUser['profile_picture'])) {
            unlink($currentUser['profile_picture']);
        }

        // Zaktualizuj ścieżkę zdjęcia w bazie danych na NULL lub odpowiednią wartość
        $query = $connection->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        $query->bind_param("i", $user_id);
        $query->execute();

        // Zaktualizuj informację o użytkowniku
        $currentUser['profile_picture'] = null; // Ustawienie na null po usunięciu
    }
}

// Pobierz zadania
$query = $connection->prepare("SELECT * FROM tasks WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$tasks = $query->get_result()->fetch_all(MYSQLI_ASSOC);

// Pobierz raporty
$query = $connection->prepare("SELECT * FROM reports WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$reports = $query->get_result()->fetch_all(MYSQLI_ASSOC);

// Aktualizacja statusu zadania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $query = $connection->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
    $query->bind_param("sii", $_POST['status'], $_POST['task_id'], $user_id); 
    $query->execute();
    header('Location: employee_dashboard.php'); // Przekierowanie
    exit();
}

// Dodawanie raportu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['report'])) {
    // Wstawienie raportu do bazy danych
    $query = $connection->prepare("INSERT INTO reports (user_id, title, report_content) VALUES (?, ?, ?)");
    $query->bind_param("iss", $user_id, $_POST['title'], $_POST['report']);
    $query->execute();

    header('Location: employee_dashboard.php'); // Przekierowanie po dodaniu raportu
    exit();
}

// Usuwanie raportu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report_id'])) {
    $query = $connection->prepare("DELETE FROM reports WHERE id = ? AND user_id = ?");
    $query->bind_param("ii", $_POST['delete_report_id'], $user_id); // Poprawne wiązanie parametrów
    $query->execute();

    header('Location: employee_dashboard.php'); // Przekierowanie po usunięciu raportu
    exit();
}

// Przesyłanie projektu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
    $uploads_dir = 'uploads/projects/';
    if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

    $file_name = basename($_FILES['project_file']['name']);
    $file_path = $uploads_dir . $file_name;

    if (move_uploaded_file($_FILES['project_file']['tmp_name'], $file_path)) {
        // Wstaw projekt do bazy danych
        $query = $connection->prepare("INSERT INTO projects (user_id, project_name, file_path) VALUES (?, ?, ?)");
        $query->bind_param("iss", $user_id, $_POST['project_name'], $file_path);
        $query->execute();

        header('Location: employee_dashboard.php'); // Przekierowanie po dodaniu projektu
        exit();
    }
}

// Pobierz projekty
$query = $connection->prepare("SELECT * FROM projects WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$projects = $query->get_result()->fetch_all(MYSQLI_ASSOC);

// Wylogowanie
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

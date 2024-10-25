<?php
require 'db_connection.php';



if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: login.php');
    exit();
}

// Pobierz dane zalogowanego użytkownika
$query = $connection->prepare("SELECT username FROM users WHERE id = ?");
$query->bind_param("i", $_SESSION['user_id']);
$query->execute();
$result = $query->get_result();
$currentUser = $result->fetch_assoc();

// Pobierz listę pracowników, zadania, raporty i projekty
$employees = $connection->query("SELECT * FROM users WHERE role = 'employee'")->fetch_all(MYSQLI_ASSOC);
$tasks = $connection->query("SELECT tasks.*, users.username AS employee_name FROM tasks JOIN users ON tasks.user_id = users.id")->fetch_all(MYSQLI_ASSOC);
$reports = $connection->query("SELECT reports.*, users.username AS employee_name FROM reports JOIN users ON reports.user_id = users.id")->fetch_all(MYSQLI_ASSOC);
$projects = $connection->query("SELECT projects.*, users.username AS employee_name FROM projects JOIN users ON projects.user_id = users.id")->fetch_all(MYSQLI_ASSOC);

$message = '';
$error_message = '';

// Obsługa przydzielania zadania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'], $_POST['task_title'])) {
    $employee_id = (int)$_POST['employee_id'];
    $task_title = trim($_POST['task_title']);
    $status = 'pending';

    if (empty($task_title)) {
        $error_message = 'Tytuł zadania jest wymagany.';
    } elseif (!$connection->query("SELECT * FROM users WHERE id = $employee_id AND role = 'employee'")->fetch_assoc()) {
        $error_message = 'Niepoprawny pracownik.';
    } else {
        $checkTask = $connection->prepare("SELECT * FROM tasks WHERE title = ? AND user_id = ?");
        $checkTask->bind_param("si", $task_title, $employee_id);
        $checkTask->execute();
        $existingTask = $checkTask->get_result()->fetch_assoc();

        if ($existingTask) {
            $error_message = 'To zadanie zostało już przydzielone temu pracownikowi.';
        } else {
            $insertQuery = $connection->prepare("INSERT INTO tasks (title, user_id, status) VALUES (?, ?, ?)");
            $insertQuery->bind_param("sis", $task_title, $employee_id, $status);
            $insertQuery->execute();
            $message = "Zadanie '$task_title' zostało przydzielone pracownikowi.";
            header('Location: manager_dashboard.php');
            exit();
        }
    }
}

// Obsługa usuwania raportu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report_id'])) {
    $report_id = (int)$_POST['delete_report_id'];
    $deleteQuery = $connection->prepare("DELETE FROM reports WHERE id = ?");
    $deleteQuery->bind_param("i", $report_id);
    if ($deleteQuery->execute()) {
        $message = 'Raport został usunięty.';
    } else {
        $error_message = 'Nie udało się usunąć raportu.';
    }
    header('Location: manager_dashboard.php'); // Przekierowanie po usunięciu
    exit();
}

// Obsługa usuwania zadania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id'])) {
    $task_id = (int)$_POST['delete_task_id'];
    $deleteQuery = $connection->prepare("DELETE FROM tasks WHERE id = ?");
    $deleteQuery->bind_param("i", $task_id);
    if ($deleteQuery->execute()) {
        $message = 'Zadanie zostało usunięte.';
    } else {
        $error_message = 'Nie udało się usunąć zadania.';
    }
    header('Location: manager_dashboard.php'); // Przekierowanie po usunięciu
    exit();
}

// Obsługa usuwania projektu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_id'])) {
    $project_id = (int)$_POST['delete_project_id'];
    $deleteQuery = $connection->prepare("DELETE FROM projects WHERE id = ?");
    $deleteQuery->bind_param("i", $project_id);
    if ($deleteQuery->execute()) {
        $message = 'Projekt został usunięty.';
    } else {
        $error_message = 'Nie udało się usunąć projektu.';
    }
    header('Location: manager_dashboard.php'); // Przekierowanie po usunięciu
    exit();
}
// Obsługa pobierania projektu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_project_id'])) {
    $project_id = (int)$_POST['download_project_id'];

    // Pobierz ścieżkę do pliku projektu z bazy danych
    $query = $connection->prepare("SELECT file_path FROM projects WHERE id = ?");
    $query->bind_param("i", $project_id);
    $query->execute();
    $result = $query->get_result();
    $project = $result->fetch_assoc();

    if ($project && file_exists($project['file_path'])) {
        // Ustaw nagłówki, aby wymusić pobieranie pliku
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($project['file_path']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($project['file_path']));
        readfile($project['file_path']);
        exit();
    } else {
        $error_message = 'Plik nie istnieje lub został usunięty.';
    }
}



// Wylogowanie
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

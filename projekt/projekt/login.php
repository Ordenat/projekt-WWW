<?php
session_start();
require 'db_connection.php';

// Przekierowanie, jeśli użytkownik jest już zalogowany
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errorMessage = '';

// Obsługa formularza logowania
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Walidacja danych
    if (empty($username) || empty($password)) {
        $errorMessage = 'Wszystkie pola są wymagane.';
    } else {
        // Zapytanie do bazy danych
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Sprawdzenie hasła
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Przekierowanie w zależności od roli
                $redirectPage = match ($user['role']) {
                    'admin' => 'admin_dashboard.php',
                    'manager' => 'manager_dashboard.php',
                    'employee' => 'employee_dashboard.php',
                    default => 'index.php',
                };
                header("Location: $redirectPage");
                exit();
            } else {
                $errorMessage = 'Niepoprawna nazwa użytkownika lub hasło.';
            }
        } else {
            $errorMessage = 'Niepoprawna nazwa użytkownika lub hasło.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Logowanie</h2>
        <?php if ($errorMessage): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Nazwa użytkownika:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Zaloguj się</button>
            </div>
        </form>
    </div>
</body>
</html>

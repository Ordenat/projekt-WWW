    <?php
    // Obsługa wylogowania
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Sprawdzenie, czy użytkownik jest administratorem
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }

    // Fetch current user's data
    $query = $connection->prepare("SELECT username FROM users WHERE id = ?");
    $query->bind_param("i", $_SESSION['user_id']);
    $query->execute();
    $currentUser = $query->get_result()->fetch_assoc();

    // Fetch all users
    $users = $connection->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);

    // Obsługa dodawania nowego użytkownika
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
        $username = trim($_POST['username']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $role = trim($_POST['role']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

        // Sprawdzenie, czy nazwa użytkownika lub e-mail już istnieje
        $checkUserQuery = $connection->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $checkUserQuery->bind_param("ss", $username, $email);
        $checkUserQuery->execute();
        $result = $checkUserQuery->get_result();

        if ($result->num_rows > 0) {
            // Sprawdzanie, który z pól spowodował błąd
            $existingUser = $result->fetch_assoc();
            if ($existingUser['username'] === $username) {
                $message = 'Użytkownik o tej nazwie już istnieje.';
            } elseif ($existingUser['email'] === $email) {
                $message = 'Użytkownik z tym adresem e-mail już istnieje.';
            }
        } elseif ($email && $username && $password && $role) {
            $insertQuery = $connection->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $insertQuery->bind_param("ssss", $username, $email, $password, $role);
            if ($insertQuery->execute()) {
                $message = 'Nowy użytkownik został dodany.';
                header('Location: admin_dashboard.php'); // Przekierowanie po dodaniu użytkownika
                exit();
            } else {
                $message = 'Wystąpił problem z dodaniem użytkownika.';
            }
            $insertQuery->close(); // Zamknięcie zapytania
        } else {
            $message = 'Niepoprawne dane. Upewnij się, że wszystkie pola są wypełnione poprawnie.';
        }

        // Zamknięcie zapytania
        $checkUserQuery->close();
    }

    // Obsługa usuwania użytkownika
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Rozpoczęcie transakcji
    $connection->begin_transaction();

    try {
        // Usuwanie raportów powiązanych z użytkownikiem
        $deleteReportsQuery = $connection->prepare("DELETE FROM reports WHERE user_id = ?");
        $deleteReportsQuery->bind_param("i", $delete_id);
        $deleteReportsQuery->execute();

        // Usuwanie projektów powiązanych z użytkownikiem
        $deleteProjectsQuery = $connection->prepare("DELETE FROM projects WHERE user_id = ?");
        $deleteProjectsQuery->bind_param("i", $delete_id);
        $deleteProjectsQuery->execute();

        // Usuwanie użytkownika
        $deleteUserQuery = $connection->prepare("DELETE FROM users WHERE id = ?");
        $deleteUserQuery->bind_param("i", $delete_id);
        $deleteUserQuery->execute();

        // Zatwierdzenie transakcji
        $connection->commit();
        $message = 'Użytkownik oraz powiązane dane zostały usunięte.';
    } catch (Exception $e) {
        // W przypadku błędu, wycofanie transakcji
        $connection->rollback();
        $message = 'Wystąpił problem z usunięciem użytkownika: ' . $e->getMessage();
    } finally {
        // Zamknięcie zapytań
        if (isset($deleteReportsQuery)) $deleteReportsQuery->close();
        if (isset($deleteProjectsQuery)) $deleteProjectsQuery->close();
        if (isset($deleteUserQuery)) $deleteUserQuery->close();
    }

    header('Location: admin_dashboard.php'); // Przekierowanie po usunięciu użytkownika
    exit();
}

    // Obsługa edytowania i aktualizacji użytkownika
    $userToEdit = null; // Inicjalizacja zmiennej
    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $editQuery = $connection->prepare("SELECT * FROM users WHERE id = ?");
        $editQuery->bind_param("i", $edit_id);
        $editQuery->execute();
        $userToEdit = $editQuery->get_result()->fetch_assoc();
        $editQuery->close(); // Zamknięcie zapytania
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
        $update_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $role = trim($_POST['role']);
        $password = trim($_POST['password']); 

        if ($email && $username && $role) {
            // Sprawdzenie, czy nazwa użytkownika już istnieje
            $checkUserQuery = $connection->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
            $checkUserQuery->bind_param("si", $username, $update_id);
            $checkUserQuery->execute();
            $userResult = $checkUserQuery->get_result();

            // Sprawdzenie, czy adres e-mail już istnieje (tylko jeśli zmieniono e-mail)
            $currentUserQuery = $connection->prepare("SELECT email FROM users WHERE id = ?");
            $currentUserQuery->bind_param("i", $update_id);
            $currentUserQuery->execute();
            $currentUserEmail = $currentUserQuery->get_result()->fetch_assoc()['email'];

            // Tylko sprawdzaj e-mail, jeśli jest inny niż aktualny
            $emailIsDuplicate = false;
            if ($email !== $currentUserEmail) {
                $checkEmailQuery = $connection->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
                $checkEmailQuery->bind_param("si", $email, $update_id);
                $checkEmailQuery->execute();
                $emailResult = $checkEmailQuery->get_result();

                if ($emailResult->num_rows > 0) {
                    $emailIsDuplicate = true; // Ustal flagę
                }
                $checkEmailQuery->close(); // Zamknięcie zapytania
            }

            // Jeśli są błędy
            if ($userResult->num_rows > 0) {
                $message = 'Użytkownik o tej nazwie już istnieje.';
            } elseif ($emailIsDuplicate) {
                $message = 'Adres e-mail jest już zajęty przez innego użytkownika.';
            } else {
                // Jeśli nie ma błędów, aktualizujemy użytkownika
                $updateQuery = $connection->prepare($password ? 
                    "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?" : 
                    "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                    
                if ($password) {
                    $password = password_hash($password, PASSWORD_BCRYPT);
                    $updateQuery->bind_param("ssssi", $username, $email, $role, $password, $update_id);
                } else {
                    $updateQuery->bind_param("sssi", $username, $email, $role, $update_id);
                }

                // Wykonanie zapytania aktualizującego
                if ($updateQuery->execute()) {
                    $message = 'Użytkownik został zaktualizowany.';
                } else {
                    $message = 'Wystąpił problem z aktualizacją użytkownika: ' . $connection->error;
                }
                $updateQuery->close(); // Zamknięcie zapytania
                header('Location: admin_dashboard.php'); // Przekierowanie po aktualizacji użytkownika
                exit();
            }

            // Zamknięcie zapytań
            $checkUserQuery->close();
            $currentUserQuery->close();
        } else {
            $message = 'Niepoprawne dane. Upewnij się, że wszystkie pola są wypełnione poprawnie.';
        }
    }


    ?>

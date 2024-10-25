<?php
session_start();
require 'db_connection.php';
require 'adminphp.php'

?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpit Administratora</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Pulpit Administratora</h2>
        <p>Witaj, <?= htmlspecialchars($currentUser['username']) ?>! Możesz zarządzać użytkownikami.</p>

        <h3>Lista użytkowników:</h3>
        <label for="sortSelect">Sortuj według:</label>
        <select id="sortSelect">
            <option value="username">Nazwa użytkownika</option>
            <option value="email">E-mail</option>
            <option value="role">Rola</option>
        </select>
        <ul id="userList">
            <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                    <li data-username="<?= htmlspecialchars($user['username']) ?>" 
                        data-email="<?= htmlspecialchars($user['email']) ?>" 
                        data-role="<?= htmlspecialchars($user['role']) ?>">
                        <?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['email']) ?> - Rola: <?= htmlspecialchars($user['role']) ?>
                        <form action="admin_dashboard.php" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                            <button type="submit" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</button>
                        </form>
                        <form action="admin_dashboard.php" method="POST" style="display:inline;">
                            <input type="hidden" name="edit_id" value="<?= $user['id'] ?>">
                            <button type="submit">Edytuj</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Brak użytkowników w systemie.</li>
            <?php endif; ?>
        </ul>

        <h3>Dodaj nowego użytkownika:</h3>
        <form action="admin_dashboard.php" method="POST">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <select name="role" required>
                <option value="employee">Pracownik</option>
                <option value="manager">Menedżer</option>
                <option value="admin">Administrator</option>
            </select>
            <button type="submit">Dodaj użytkownika</button>
        </form>

        <?php if ($userToEdit): ?>
            <h3>Edytuj użytkownika:</h3>
            <form action="admin_dashboard.php" method="POST">
                <input type="hidden" name="user_id" value="<?= $userToEdit['id'] ?>">
                <input type="text" name="username" value="<?= htmlspecialchars($userToEdit['username']) ?>" required>
                <input type="email" name="email" value="<?= htmlspecialchars($userToEdit['email']) ?>" required>
                <input type="password" name="password" placeholder="Nowe hasło (pozostaw puste, aby nie zmieniać)">
                <select name="role" required>
                    <option value="employee" <?= $userToEdit['role'] == 'employee' ? 'selected' : '' ?>>Pracownik</option>
                    <option value="manager" <?= $userToEdit['role'] == 'manager' ? 'selected' : '' ?>>Menedżer</option>
                    <option value="admin" <?= $userToEdit['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <button type="submit" name="update_user">Aktualizuj użytkownika</button>
            </form>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="admin_dashboard.php" method="POST" style="margin-top: 20px;">
            <button type="submit" name="logout">Wyloguj się</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const userList = document.getElementById('userList');
    const sortSelect = document.getElementById('sortSelect');

    sortSelect.addEventListener('change', function() {
        const users = Array.from(userList.children);

        users.sort((a, b) => {
            const key = sortSelect.value; // uzyskaj wybrane kryterium
            const aValue = a.getAttribute(`data-${key}`).toLowerCase();
            const bValue = b.getAttribute(`data-${key}`).toLowerCase();

            return aValue.localeCompare(bValue);
        });

        // Usuń wszystkie dzieci (użytkowników) z listy
        while (userList.firstChild) {
            userList.removeChild(userList.firstChild);
        }

        // Dodaj posortowane elementy z powrotem do listy
        users.forEach(user => {
            userList.appendChild(user);
        });
    });
});

        </script>
</body>
</html>

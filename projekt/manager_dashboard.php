<?php
session_start();
require 'db_connection.php';
require 'managerphp.php'; // Kod PHP z logiką menedżera
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpit Menedżera</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Pulpit Menedżera</h2>
        <p>Witaj, <?= htmlspecialchars($currentUser['username']) ?>! Możesz tutaj zarządzać swoim zespołem.</p>

        <!-- Komunikaty sukcesu i błędów -->
        <?php if (!empty($message)): ?>
            <div class="success-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Lista pracowników -->
        <h3>Lista pracowników:</h3>
        <ul>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $employee): ?>
                    <li><?= htmlspecialchars($employee['username']) ?> - <?= htmlspecialchars($employee['email']) ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Brak pracowników w zespole.</li>
            <?php endif; ?>
        </ul>

        <!-- Formularz przypisania zadania -->
        <h3>Przydziel zadanie:</h3>
        <form action="manager_dashboard.php" method="POST">
            <select name="employee_id" required>
                <option value="">Wybierz pracownika</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['username']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="task_title" placeholder="Tytuł zadania" required>
            <button type="submit">Przydziel zadanie</button>
        </form>

        <!-- Wyszukiwanie zadań -->
        <h3>Wyszukiwanie zadań:</h3>
        <input type="text" id="searchInput" placeholder="Wyszukaj zadania...">

        <!-- Lista zadań -->
        <h3>Zadania:</h3>
        <table id="tasksTable">
            <thead>
                <tr>
                    <th>Tytuł zadania</th>
                    <th>Pracownik</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['employee_name']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td>
                            <form action="manager_dashboard.php" method="POST">
                                <input type="hidden" name="delete_task_id" value="<?= $task['id'] ?>">
                                <button type="submit">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Lista raportów -->
        <h3>Raporty:</h3>
        <table>
            <thead>
                <tr>
                    <th>Tytuł raportu</th>
                    <th>Pracownik</th>
                    <th>Data utworzenia</th>
                    <th>Treść</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['title']) ?></td>
                            <td><?= htmlspecialchars($report['employee_name']) ?></td>
                            <td><?= htmlspecialchars($report['created_at']) ?></td>
                            <td><?= htmlspecialchars($report['report_content']) ?></td>
                            <td>
                                <form action="manager_dashboard.php" method="POST">
                                    <input type="hidden" name="delete_report_id" value="<?= $report['id'] ?>">
                                    <button type="submit">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Brak raportów.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

       <!-- Lista projektów -->
<h3>Projekty:</h3>
<table>
    <thead>
        <tr>
            <th>Nazwa projektu</th>
            <th>Pracownik</th>
            <th>Data utworzenia</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['project_name']) ?></td>
                    <td><?= htmlspecialchars($project['employee_name']) ?></td>
                    <td><?= htmlspecialchars($project['created_at']) ?></td>
                    <td>
                        <form action="manager_dashboard.php" method="POST" style="display:inline;">
                            <input type="hidden" name="download_project_id" value="<?= $project['id'] ?>">
                            <button type="submit">Pobierz</button>
                        </form>
                        <form action="manager_dashboard.php" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_project_id" value="<?= $project['id'] ?>">
                            <button type="submit">Usuń</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Brak projektów.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


        <!-- Formularz wylogowania -->
        <form action="manager_dashboard.php" method="POST">
            <button type="submit" name="logout">Wyloguj się</button>
        </form>
    </div>

    <script>
        // Wyszukiwanie zadań
        const searchInput = document.getElementById('searchInput');
        const tasksTable = document.getElementById('tasksTable').getElementsByTagName('tbody')[0];
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = tasksTable.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const titleCell = rows[i].getElementsByTagName('td')[0];
                if (titleCell) {
                    const txtValue = titleCell.textContent || titleCell.innerText;
                    rows[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        });
    </script>
</body>
</html>

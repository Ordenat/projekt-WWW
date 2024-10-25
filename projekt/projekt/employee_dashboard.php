<?php
session_start();
require 'db_connection.php';
require 'employeephp.php'; // Make sure this file exists and is properly named
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpit Pracownika</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .task-pending { color: red; }
        .task-completed { color: green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Pulpit Pracownika</h2>
    <p>Witaj, <?= htmlspecialchars($currentUser['username']) ?>!</p>
    
    <div class="profile">
    <h2>Zdjęcie profilowe</h2>
    <?php if (!empty($currentUser['profile_picture']) && file_exists($currentUser['profile_picture'])): ?>
        <img src="<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" alt="Zdjęcie profilowe" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
        <form action="" method="POST">
            <button type="submit" name="delete_picture" onclick="return confirm('Czy na pewno chcesz usunąć zdjęcie profilowe?');">Usuń zdjęcie</button>
        </form>
    <?php else: ?>
        <p>Nie masz jeszcze zdjęcia profilowego.</p>
    <?php endif; ?>

    <!-- Formularz do dodawania zdjęcia profilowego -->
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="profile_picture">Wybierz zdjęcie:</label>
        <input type="file" name="profile_picture" id="profile_picture" required>
        <button type="submit">Dodaj zdjęcie</button>
    </form>
</div>

    <h3>Przydzielone zadania:</h3>
    <ul id="task-list">
        <?php if ($tasks): ?>
            <?php foreach ($tasks as $task): ?>
                <li class="<?= $task['status'] === 'pending' ? 'task-pending' : 'task-completed' ?>">
                    <strong><?= htmlspecialchars($task['title']) ?></strong> - Status: <span><?= htmlspecialchars($task['status']) ?></span>
                    <form action="employee_dashboard.php" method="POST" style="display:inline;">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>W toku</option>
                            <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Zakończone</option>
                        </select>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Brak przydzielonych zadań.</li>
        <?php endif; ?>
    </ul>

    <h3>Dodaj raport</h3>
    <form action="employee_dashboard.php" method="POST">
        <input type="text" name="title" placeholder="Tytuł raportu" required>
        <textarea name="report" placeholder="Wprowadź treść raportu" required></textarea>
        <button type="submit">Dodaj raport</button>
    </form>
    <div id="report-message" style="margin-top: 10px;">
        <?php if (isset($reportMessage)) echo $reportMessage; ?>
    </div>

    <h3>Moje raporty:</h3>
    <ul id="report-list">
        <?php if ($reports): ?>
            <?php foreach ($reports as $report): ?>
                <li id="report-<?= $report['id'] ?>">
                    <strong><?= htmlspecialchars($report['title']) ?></strong>
                    <p><?= htmlspecialchars($report['report_content']) ?></p>
                    <form action="employee_dashboard.php" method="POST" style="display:inline;">
                        <input type="hidden" name="delete_report_id" value="<?= $report['id'] ?>">
                        <button type="submit">Usuń raport</button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Brak raportów.</li>
        <?php endif; ?>
    </ul>

    <h3>Wyślij projekt</h3>
    <form action="employee_dashboard.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="project_name" placeholder="Nazwa projektu" required>
        <input type="file" name="project_file" accept=".pdf,.docx,.xlsx" required>
        <button type="submit">Wyślij projekt</button>
    </form>
    <div id="project-message" style="margin-top: 10px;">
        <?php if (isset($projectMessage)) echo $projectMessage; ?>
    </div>

    <h3>Moje projekty:</h3>
    <ul id="project-list">
        <?php if ($projects): ?>
            <?php foreach ($projects as $project): ?>
                <li>
                    <strong><?= htmlspecialchars($project['project_name']) ?></strong> - 
                    <a href="<?= htmlspecialchars($project['file_path']) ?>" target="_blank">Pobierz plik</a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Brak przesłanych projektów.</li>
        <?php endif; ?>
    </ul>

    <form action="employee_dashboard.php" method="POST">
        <button type="submit" name="logout">Wyloguj się</button>
    </form>
</div>

</body>
</html>
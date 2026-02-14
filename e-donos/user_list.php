<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("Musisz być zalogowany.");
}

$usersFile = __DIR__ . "/../secure/users.json";
$users = json_decode(file_get_contents($usersFile), true);

// Automatyczne dodanie followers/following
$changed = false;
foreach ($users as &$u) {
    if (!isset($u['followers'])) { $u['followers'] = []; $changed = true; }
    if (!isset($u['following'])) { $u['following'] = []; $changed = true; }
}
unset($u);

if ($changed) {
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$search = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Użytkownicy</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #0f1624;
        font-family: Arial, sans-serif;
        color: white;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
    }

    .container {
        width: 80vw;
        max-width: 70vw;
        margin-top: 5vh;
        background: rgba(255,255,255,0.05);
        padding: 4vh 4vw;
        border-radius: 2vh;
        backdrop-filter: blur(1vh);
    }

    h1 {
        text-align: center;
        font-size: 4vh;
        margin-bottom: 4vh;
    }

    /* ⭐ Pasek wyszukiwania + powrót w jednej linii */
    .search-row {
        display: flex;
        align-items: center;
        gap: 1vw;
        margin-bottom: 4vh;
        flex-wrap: wrap;
    }

    .search-row input[type="text"] {
        flex: 1;
        min-width: 30%;
        padding: 1.5vh 1vw;
        border-radius: 1vh;
        border: none;
        font-size: 2vh;
        outline: none;
    }

    .search-row button {
        padding: 1.5vh 2vw;
        border: none;
        border-radius: 1vh;
        background: #3a4460;
        color: white;
        font-size: 2vh;
        cursor: pointer;
        transition: 0.2s;
        white-space: nowrap;
    }

    .search-row button:hover {
        background: #25324a;
    }

    
    .powrot {
        background: #3a4460;
    }

    .user-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 2vh;
    }

    .user-item {
        background: rgba(255,255,255,0.08);
        padding: 2vh 2vw;
        border-radius: 1.5vh;
        font-size: 2.2vh;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: 0.2s;
    }

    .user-item:hover {
        background: rgba(255,255,255,0.15);
    }

    .user-item a {
        color: #9fc5ff;
        text-decoration: none;
        font-weight: bold;
    }

    .user-item a:hover {
        text-decoration: underline;
    }

    
    @media (max-width: 600px) {

        .container {
            width: 90vw;
            max-width: 90vw;
            padding: 5vh 5vw;
        }

        .search-row {
            gap: 2vh;
        }

        .search-row input[type="text"] {
            width: 100%;
            font-size: 2.4vh;
            padding: 2vh;
        }

        .search-row button {
            width: 100%;
            font-size: 2.4vh;
            padding: 2vh;
        }

        .user-item {
            font-size: 2.6vh;
            padding: 2.5vh 3vw;
        }
    }
</style>

</head>
<body>

<div class="container">

    <h1>Lista użytkowników</h1>

    
    <form method="GET" class="search-row">
        <input type="text" name="search" placeholder="Szukaj użytkownika..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Szukaj</button>
        <button type="button" class="powrot" onclick="location.href='edonos.php'">Powrót</button>
    </form>

    <ul class="user-list">
        <?php foreach ($users as $username => $data): ?>
            <?php if ($search === '' || stripos($username, $search) !== false): ?>
                <li class="user-item">
                    <a href="profil.php?user=<?= urlencode($username) ?>">
                        <?= htmlspecialchars($username) ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

</div>

</body>
</html>

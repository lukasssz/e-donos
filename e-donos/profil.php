<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("Musisz być zalogowany.");
}

if (!isset($_GET['user'])) {
    die("Brak użytkownika.");
}

$profileUser = $_GET['user'];

$usersFile = __DIR__ . "/../secure/users.json";
$users = json_decode(file_get_contents($usersFile), true);

// Dodaj followers/following jeśli brakuje
$changed = false;
foreach ($users as &$u) {
    if (!isset($u['followers'])) { $u['followers'] = []; $changed = true; }
    if (!isset($u['following'])) { $u['following'] = []; $changed = true; }
}
unset($u);

if ($changed) {
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if (!isset($users[$profileUser])) {
    die("Użytkownik nie istnieje.");
}

$u = $users[$profileUser];

// Wczytaj donosy
$donosyFile = __DIR__ . "/../secure/donosy1.json";
$donosy = json_decode(file_get_contents($donosyFile), true);

// Donosy użytkownika
$userDonosy = array_values(array_filter($donosy, fn($d) => $d['sender'] === $profileUser));
$totalDonosy = count($userDonosy);

$isFollowing = in_array($_SESSION['user'], $u['followers']);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Profil <?= htmlspecialchars($profileUser) ?></title>

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
        margin-bottom: 3vh;
    }

    .profile-info {
        text-align: center;
        margin-bottom: 4vh;
        font-size: 2.2vh;
    }

    .follow-btn {
        padding: 1.5vh 3vw;
        background: #1a2333;
        color: white;
        border: none;
        border-radius: 1vh;
        font-size: 2vh;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 2vh;
    }

    .follow-btn:hover {
        background: #25324a;
    }

    /* ⭐ NOWY PRZYCISK POWRÓT — POD FORMULARZEM */
    .powrot {
        display: block;
        width: 100%;
        padding: 2vh 0;
        font-size: 2.4vh;
        background: #1a2333;
        color: white;
        border: none;
        border-radius: 1vh;
        box-shadow: 0 0.8vh 2vh rgba(0,0,0,0.15);
        cursor: pointer;
        transition: 0.25s ease;
        margin: 2vh 0 4vh 0;
    }

    .powrot:hover {
        background: #25324a;
    }

    .section-title {
        font-size: 3vh;
        margin-top: 4vh;
        margin-bottom: 2vh;
        border-bottom: 0.3vh solid rgba(255,255,255,0.2);
        padding-bottom: 1vh;
    }

    .donos-card {
        background: rgba(255,255,255,0.08);
        padding: 2vh 2vw;
        border-radius: 1.5vh;
        margin-bottom: 2vh;
        transition: 0.2s;
        cursor: pointer;
    }

    .donos-title {
        font-size: 2.2vh;
        font-weight: bold;
        margin-bottom: 1vh;
    }

    .donos-text {
        font-size: 2vh;
        opacity: 0.9;
        margin-bottom: 1vh;
    }

    #showMoreBtn {
        padding: 1.5vh 3vw;
        background: #1a2333;
        color: white;
        border: none;
        border-radius: 1vh;
        font-size: 2vh;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 2vh;
        display: none;
    }

    #showMoreBtn:hover {
        background: #25324a;
    }

    /* ⭐ MOBILE BOOST */
    @media (max-width: 600px) {
        .container {
            width: 90vw;
            max-width: 90vw;
            padding: 5vh 5vw;
        }

        .powrot {
            font-size: 3vh;
            padding: 2.5vh 0;
            border-radius: 1.5vh;
        }

        .donos-card {
            padding: 3vh 3vw;
        }

        .donos-title {
            font-size: 2.6vh;
        }

        .donos-text {
            font-size: 2.4vh;
        }

        #showMoreBtn {
            font-size: 2.6vh;
            padding: 2vh 0;
        }
    }
</style>

</head>
<body>

<div class="container">

    <h1>Profil: <?= htmlspecialchars($profileUser) ?></h1>

    <div class="profile-info">
        <p><strong>Bio:</strong> <?= htmlspecialchars($u['bio'] ?? "") ?></p>
        <p>Obserwują: <?= count($u['followers']) ?></p>
        <p>Obserwowani: <?= count($u['following']) ?></p>

        <?php if ($_SESSION['user'] !== $profileUser): ?>
            <?php if ($isFollowing): ?>
                <form action="unfollow.php" method="POST">
                    <input type="hidden" name="target" value="<?= htmlspecialchars($profileUser) ?>">
                    <button class="follow-btn" type="submit">Przestań obserwować</button>
                </form>
            <?php else: ?>
                <form action="follow.php" method="POST">
                    <input type="hidden" name="target" value="<?= htmlspecialchars($profileUser) ?>">
                    <button class="follow-btn" type="submit">Obserwuj</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
    <button class="powrot" onclick="location.href='user_list.php'">Powrót</button>

    <h2 class="section-title">Donosy użytkownika (<?= $totalDonosy ?>)</h2>

    <div id="donosContainer"></div>

    <button id="showMoreBtn" onclick="showMore()">Pokaż więcej</button>

</div>

<script>
    const allDonosy = <?= json_encode($userDonosy) ?>;
    let shown = 0;

    function renderDonos(d) {
        return `
            <div class="donos-card">
                <div class="donos-title">${d.date ?? ''}</div>
                <div class="donos-text">${d.description ?? ''}</div>
            </div>
        `;
    }

    function showMore() {
        const container = document.getElementById("donosContainer");

        let limit = shown === 0 ? 2 : shown + 5;

        for (let i = shown; i < Math.min(limit, allDonosy.length); i++) {
            container.innerHTML += renderDonos(allDonosy[i]);
        }

        shown = Math.min(limit, allDonosy.length);

        const btn = document.getElementById("showMoreBtn");
        btn.style.display = shown < allDonosy.length ? "block" : "none";
    }

    // Initial load (first 2)
    showMore();
</script>

</body>
</html>

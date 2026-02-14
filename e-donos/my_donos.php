<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

$file = __DIR__ . '/../secure/donosy1.json';
$data = json_decode(file_get_contents($file), true);


$my = array_filter($data, function($d) use ($user) {
    return isset($d['sender']) && $d['sender'] === $user;
});


usort($my, function($a, $b) {
    return $b['time'] - $a['time'];
});
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Moje donosy</title>

<style>
body {
    background: #f5f5f5;
    font-family: sans-serif;
    padding: 5vh 4vw;
}

.card {
    background: white;
    padding: 3vh 2vw;
    margin-bottom: 3vh;
    border-radius: 2vh;
    box-shadow: 0 0 2vh rgba(0,0,0,0.1);
    width: 94%;
}

.deleteBtn {
    padding: 1vh 2vw;
    background: #ff3333;
    color: white;
    border: none;
    border-radius: 1vh;
    cursor: pointer;
    font-size: 2vh;
    float: right;
    margin-top: 9vh;
}
.powrot {
    padding: 1.5vh 2.5vw;
    font-size: 2vh;
    background: #1a2333;
    color: white;
    border: none;
    border-radius: 1vh;
    box-shadow: 0 0.8vh 2vh rgba(0,0,0,0.15);
    cursor: pointer;
    transition: 0.25s ease;
    position: fixed;
    top: 5vh;
    right: 10vw;
    z-index: 9999;
}
</style>

</head>
<body>

<h1>Twoje wysłane donosy</h1>

<?php if (empty($my)): ?>
    <p>Nie wysłałeś jeszcze żadnych donosów.</p>
<?php endif; ?>

<?php foreach ($my as $d): ?>
<div class="card">

    <button class="deleteBtn"
        onclick="if(confirm('Na pewno usunąć?')) location.href='delete_donos.php?id=<?= $d['id'] ?>'">
        Usuń
    </button>

    <p><b>Data wysłania:</b> <?= date("d.m.Y H:i", $d['time']) ?></p>
    <p><b>Miejsce:</b> <?= htmlspecialchars($d['place']) ?></p>
    <p><b>Data zdarzenia:</b> <?= htmlspecialchars($d['date']) ?></p>
    <p><b>Opis:</b><br><?= nl2br(htmlspecialchars($d['description'])) ?></p>

</div>
<?php endforeach; ?>

<button class="powrot" onclick="location.href='edonos.php'">Powrót</button>

</body>
</html>

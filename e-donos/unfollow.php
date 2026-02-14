<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("Musisz być zalogowany.");
}

$target = $_POST['target'] ?? null;
if (!$target) die("Brak użytkownika.");

$usersFile = __DIR__ . "/../secure/users.json";
$users = json_decode(file_get_contents($usersFile), true);

// Automatyczne dodanie followers/following
foreach ($users as &$u) {
    if (!isset($u['followers'])) $u['followers'] = [];
    if (!isset($u['following'])) $u['following'] = [];
}
unset($u);

if (!isset($users[$target])) die("Użytkownik nie istnieje.");

$current = $_SESSION['user'];

// Usuń obserwowanie
$users[$target]['followers'] = array_values(array_diff($users[$target]['followers'], [$current]));
$users[$current]['following'] = array_values(array_diff($users[$current]['following'], [$target]));

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: profil.php?user=" . urlencode($target));
exit;

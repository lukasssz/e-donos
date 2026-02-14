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

// Dodaj obserwowanie
if (!in_array($current, $users[$target]['followers'])) {
    $users[$target]['followers'][] = $current;
}

if (!in_array($target, $users[$current]['following'])) {
    $users[$current]['following'][] = $target;
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: profil.php?user=" . urlencode($target));
exit;

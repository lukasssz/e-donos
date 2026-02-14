<?php
// verify.php
session_start();

$usersFile = __DIR__ . "/../secure/users.json";
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

$code = $_GET['code'] ?? null;

if (!$code) {
    die("Brak kodu weryfikacyjnego.");
}

$found = false;

foreach ($users as $login => $data) {

    if (($data['verification_code'] ?? '') === $code) {

        $users[$login]['verified'] = true;
        $users[$login]['verification_code'] = null;

        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $_SESSION['user'] = $login;

        header("Location: edonos.php?status=verified");
        exit;
    }
}

echo "Nieprawidłowy lub wygasły kod weryfikacyjny.";

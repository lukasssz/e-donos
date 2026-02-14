<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

$loginInput = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($loginInput === '' || $password === '') {
    exit("Wypełnij wszystkie pola.");
}

$usersFile = __DIR__ . '/../secure/users.json';


$users = file_exists($usersFile)
    ? json_decode(file_get_contents($usersFile), true)
    : [];


$changed = false;

foreach ($users as &$u) {
    if (!isset($u['followers'])) {
        $u['followers'] = [];
        $changed = true;
    }
    if (!isset($u['following'])) {
        $u['following'] = [];
        $changed = true;
    }
}
unset($u);

if ($changed) {
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}


$foundUser = null;
$foundLogin = null;

foreach ($users as $login => $data) {
    if ($login === $loginInput || strtolower($data['email']) === strtolower($loginInput)) {
        $foundUser = $data;
        $foundLogin = $login;
        break;
    }
}

if (!$foundUser) {
    exit("Nie znaleziono użytkownika.");
}


if (!password_verify($password, $foundUser['password'])) {
    exit("Nieprawidłowe hasło.");
}


$_SESSION['user'] = $foundLogin;


header("Location: edonos.php");
exit;

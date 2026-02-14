<?php
session_start();
header("Content-Type: text/plain");

if (!isset($_SESSION['user'])) {
    echo "Musisz być zalogowany.";
    exit;
}

$login = $_SESSION['user'];

$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$new_password_confirm = $_POST['new_password_confirm'] ?? '';

if ($old_password === '' || $new_password === '' || $new_password_confirm === '') {
    echo "Wszystkie pola są wymagane.";
    exit;
}

if ($new_password !== $new_password_confirm) {
    echo "Nowe hasła nie są takie same.";
    exit;
}

$usersFile = __DIR__ . '/../secure/users.json';
$users = json_decode(file_get_contents($usersFile), true);

if (!isset($users[$login])) {
    echo "Błąd: użytkownik nie istnieje.";
    exit;
}

$user = $users[$login];

if (!password_verify($old_password, $user['password'])) {
    echo "Stare hasło jest nieprawidłowe.";
    exit;
}

$users[$login]['password'] = password_hash($new_password, PASSWORD_DEFAULT);

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "SUCCESS";

<?php
session_start();
header("Content-Type: text/plain");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../secure/PHPMailer/src/Exception.php';
require __DIR__ . '/../secure/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../secure/PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user'])) {
    echo "Musisz być zalogowany.";
    exit;
}

$login = $_SESSION['user'];

$new_email = $_POST['new_email'] ?? '';
$password = $_POST['password'] ?? '';

if ($new_email === '' || $password === '') {
    echo "Wszystkie pola są wymagane.";
    exit;
}

$usersFile = __DIR__ . '/../secure/users.json';
$users = json_decode(file_get_contents($usersFile), true);

$user = $users[$login];

if (!password_verify($password, $user['password'])) {
    echo "Hasło jest nieprawidłowe.";
    exit;
}

foreach ($users as $u) {
    if (strtolower($u['email']) === strtolower($new_email)) {
        echo "Ten email jest już używany.";
        exit;
    }
}

$verificationCode = bin2hex(random_bytes(16));

$users[$login]['email'] = $new_email;
$users[$login]['verified'] = false;
$users[$login]['verification_code'] = $verificationCode;

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$verifyLink = "https://e-donos.com.pl/verify.php?code=$verificationCode";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->AuthType = 'LOGIN';
    $mail->Username = '3donosy@gmail.com';
    $mail->Password = 'shrxhnmpdvnhjiuo';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('3donosy@gmail.com', 'E-Donos');
    $mail->addAddress($new_email);

    $mail->Subject = 'Potwierdzenie nowego adresu email';
    $mail->Body = "Kliknij link, aby potwierdzić nowy email:\n$verifyLink";

    $mail->send();
} catch (Exception $e) {
    echo "Błąd wysyłania maila.";
    exit;
}

echo "SUCCESS";

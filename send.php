<?php

$ip = $_SERVER['REMOTE_ADDR'];
$file = 'C:/xampp/secure/ip.log';

$limitSeconds = 10; 
$time = time();

$entries = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];

foreach ($entries as $line) {
    [$savedIp, $savedTime] = explode('|', $line);
    if ($savedIp === $ip && ($time - (int)$savedTime) < $limitSeconds) {
        http_response_code(429);
        exit('Za często. Odczekaj chwilę ');
    }
}

file_put_contents($file, "$ip|$time\n", FILE_APPEND);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/secure/PHPMailer/src/Exception.php';
require 'C:/xampp/secure/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/secure/PHPMailer/src/SMTP.php';
$config = require 'C:/xampp/secure/config.php';

$allowedTypes = ['image/jpeg','image/png','image/webp','video/mp4','video/quicktime'];
$maxSize = 5 * 1024 * 1024; // 5MB

function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$sender_name  = clean($_POST['t_imię']);
$sender_class = clean($_POST['t_klasa']);
$target_name  = clean($_POST['j_imię']);
$target_class = clean($_POST['j_klasa']);
$description  = clean($_POST['opis']);
$place        = clean($_POST['miejsce']);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PASS;
    $mail->SMTPSecure = 'tls'; 
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    $mail->Port = 587;

    $mail->setFrom('alodonosy@gmail.com', 'e-Donos');
    $mail->addAddress('alodonosy@gmail.com');

    // -----------------------------
    // MULTIPLE FILE UPLOAD SUPPORT
    // -----------------------------
    if (!empty($_FILES['media']['name'][0])) {

        foreach ($_FILES['media']['name'] as $index => $filename) {

            $tmp  = $_FILES['media']['tmp_name'][$index];
            $size = $_FILES['media']['size'][$index];
            $type = $_FILES['media']['type'][$index];

            if ($size > $maxSize) {
                exit("Plik {$filename} jest za duży (max 5MB)");
            }

            if (!in_array($type, $allowedTypes)) {
                exit("Niedozwolony typ pliku: {$filename}");
            }

            $mail->addAttachment($tmp, $filename);
        }
    }

    $mail->isHTML(true);
    $mail->Subject = 'NOWY DONOS';

    $mail->Body = "
<h2>Nowy donos</h2>

<b>Donoszący:</b> {$sender_name}<br>
<b>Klasa:</b> {$sender_class}<br><br>

<b>Donoszony:</b> {$target_name}<br>
<b>Klasa:</b> {$target_class}<br><br>

<b>Miejsce:</b> {$place}<br><br>

<b>Opis:</b><br>
{$description}<br><br>
";

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->send();
    echo "Donos wysłany. Czekaj na punkty.";

} catch (Exception $e) {
    echo "Błąd: {$mail->ErrorInfo}";
}

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../secure/PHPMailer/src/Exception.php';
require __DIR__ . '/../secure/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../secure/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../secure/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $source  = $_POST['source'] ?? '';
    $date    = $_POST['date'] ?? '';
    $time    = $_POST['time'] ?? '';
    $reason  = $_POST['reason'] ?? '';
    $donosId = $_POST['donos_id'] ?? 'brak ID';
    $details = $_POST['details'] ?? '';

    
$cleanDate = date("d.m.Y") . " " . $time;

}




    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom(MAIL_USER, 'e-Donosy');
        $mail->addAddress(MAIL_USER);

        $mail->isHTML(false);
        $mail->Subject = "Nowe zgłoszenie donosu ($cleanDate)";
        $mail->Body =
"Zgłoszony donos:
Data wysłania zgłoszonego donosu: $cleanDate

Powód zgłoszenia:
$reason

Dodatkowy opis:
$details
";

        $mail->send();

        header("Location: donosy.php?reported=1");
        exit;

    } catch (Exception $e) {
        echo "<pre>Błąd wysyłania maila:\n" . $mail->ErrorInfo . "</pre>";
    }


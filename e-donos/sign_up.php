<?php
// sign_up.php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../secure/PHPMailer/src/Exception.php';
require __DIR__ . '/../secure/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../secure/PHPMailer/src/SMTP.php';

require_once __DIR__ . '/../secure/config.php';

$login            = trim($_POST['login'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';


// ----------------------
// BOT PROTECTION
// ----------------------
if (!empty($_POST['website'])) exit("Wykryto bota.");

$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$logFile = __DIR__ . '/../secure/register_limit.log';

$entries = file_exists($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES) : [];
$now     = time();

$entries = array_filter($entries, fn($line) => $now - explode('|', $line)[1] < 600);

$attempts = array_filter($entries, fn($line) => explode('|', $line)[0] === $ip);

if (count($attempts) >= 5) exit("Zbyt wiele prób rejestracji z tego IP.");

$entries[] = "$ip|$now";
file_put_contents($logFile, implode("\n", $entries));


// ----------------------
// VALIDATION
// ----------------------
if ($login === '' || $email === '' || $password === '' || $password_confirm === '')
    exit("Wszystkie pola są wymagane.");

if ($password !== $password_confirm)
    exit("Hasła nie są takie same.");

$badWords = [
    "kurwa","kurwy","kurwo","kurew","kvrwa","kvrwy","k.u.r.w.a","k u r w a","ku.rwa","kur*w*a","k0rwa","kórwa",
"kutas","kutasy","kvtas","k.u.t.a.s","k0tas","kut@s","ku+as",
"chuj","chuja","chuje","chvj","huj","h.u.j","chu*j","ch0j","chuy",
"pizda","pizdy","p1zda","p1zdy","pi.zda","piźda","p!zda","piz.d.a",
"jebac","jebać","j3bac","jebany","jebana","jebane","jebani","jebie","jebią","jebiesz","jebiecie","jebal","jebala","jebalo","j.e.b.a.c","je8ac",
"pierdol","pierdoli","pierdolę","pierdolony","pierdolona","pierdolone","pierdolec","pier.dol","p1erdol","p!erdol",
"skurwiel","skurwysyn","skurwysyny","skur.wiel","skurvysyn","skurw*el",
"sukinsyn","sukin.syn","suk!nsyn",
"dziwka","dziwki","dziwk@","dz1wka","dzi.wka","dz!wka",
"szmata","szmaty","szma.ta","szm@ta",
"cwel","cwelu","cwele","cewl","cw3l","c.w.e.l",
"debil","debile","d3bil","deb1l",
"idiota","idioci","idiotka","id10ta","!diota",
"gówno","gowno","gówna","gowna","g0wno","g0wn0","g*wno",
"fuck","fuk","f*ck","f**k","f.u.c.k","fuuck","f0ck","f@ck",
"fucking","fuck3r","fucker","motherfucker","m0therfucker","mfucker",
"shit","sh1t","sh!t","bullshit","crap","sh.it","s h i t","sh!7",
"bitch","bitches","biatch","b1tch","b!tch","b*tch",
"asshole","assholes","a s s h o l e",
"dick","dicks","d1ck","d!ck","dickhead","d!ckhead",
"bastard","bas.tard","b@stard",
"slut","s1ut","sl*t",
"whore","wh0re","who.re","wh*re",
"nigger","nigga","n1gger","n1gga","niga","niger","n!gga",
"retard","ret4rd","r3tard",
"faggot","f4ggot","f@g","fa.ggot",
"cunt","c*nt","kunt",
"cock","c0ck","c*ck",
"pierdoli","p1erdoli","pier.doli","p!erdoli",
"szon","sz0n","szoń",
"lamus","lamusy",
"frajer","fraj3r",
"kretyn","kr3tyn",
"palant","pa!ant",
"idiot","!diot",
"moron","m0ron",
"jerk","j3rk",
"jackass","j@ckass"

];
foreach ($badWords as $bw)
    if (stripos($login, $bw) !== false) exit("Login zawiera niedozwolone słowa.");

if (strlen($login) < 6) exit("Login musi mieć co najmniej 6 znaków.");
if (strpos($login, ' ') !== false) exit("Login nie może zawierać spacji.");
if (!preg_match('/^[\x20-\x7E]+$/', $login)) exit("Login nie może zawierać emoji.");
if (!preg_match('/^[A-Za-z0-9_]+$/', $login)) exit("Login może zawierać tylko litery, cyfry i _.");

if (strlen($password) < 6) exit("Hasło musi mieć co najmniej 6 znaków.");
if (!preg_match('/[\W_]/', $password)) exit("Hasło musi zawierać znak specjalny.");

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    exit("Niepoprawny adres email.");


// ----------------------
// LOAD USERS
// ----------------------
$usersFile = __DIR__ . '/../secure/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (isset($users[$login])) exit("Ten login jest już zajęty.");

foreach ($users as $u)
    if (strtolower($u['email']) === strtolower($email))
        exit("Ten email jest już używany.");


// ----------------------
// CREATE ACCOUNT
// ----------------------
$hashed = password_hash($password, PASSWORD_DEFAULT);
$verificationCode = bin2hex(random_bytes(16));

$users[$login] = [
    "email"             => $email,
    "password"          => $hashed,
    "verified"          => false,
    "verification_code" => $verificationCode,
    "followers"         => [],
    "following"         => [],
    "bio"               => ""
];

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


// ----------------------
// SEND EMAIL
// ----------------------
$verifyLink = "https://e-donos.com.pl/e-donos/verify.php?code=$verificationCode";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom(MAIL_USER, 'E-Donos');
    $mail->addAddress($email);

    $mail->Subject = 'Potwierdzenie konta';
    $mail->Body    = "Kliknij link, aby aktywować konto:\n$verifyLink";

    $mail->send();

    // Po poprawnej rejestracji i wysłaniu maila:
    header("Location: edonos.php?status=unverified");
    exit;

} catch (Exception $e) {
    // Jeśli mail nie wyśle się, konto i tak jest utworzone
    echo "Błąd wysyłania maila: " . $mail->ErrorInfo;
}

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$loggedUser = $_SESSION['user']; // prawdziwy nick użytkownika

$ip = $_SERVER['REMOTE_ADDR'];
$file = __DIR__ . '/../secure/ip.log';

$limitSeconds = 10;
$time = time();

$entries = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
$newEntries = [];

// Rate limit — czyszczenie starych wpisów
foreach ($entries as $line) {
    $parts = explode('|', $line);
    if (count($parts) !== 2) continue;

    [$savedIp, $savedTime] = $parts;

    if ($savedIp === $ip && ($time - (int)$savedTime) < $limitSeconds) {
        http_response_code(429);
        exit('Za często. Odczekaj chwilę.');
    }

    if ($time - (int)$savedTime < $limitSeconds) {
        $newEntries[] = $line;
    }
}

$newEntries[] = "$ip|$time";
file_put_contents($file, implode("\n", $newEntries) . "\n");


// ----------------------
// Walidacja POST
// ----------------------
$required = ['t_imię','t_klasa','j_imię','opis','miejsce','data'];

foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        exit("Brak wymaganego pola: $field");
    }
}

function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$sender_display = clean($_POST['t_imię']); 
$sender_class   = clean($_POST['t_klasa']);
$target_name    = clean($_POST['j_imię']);
$description    = clean($_POST['opis']);
$place          = clean($_POST['miejsce']);
$data           = clean($_POST['data']);


if (strlen($description) > 500) {
    exit("Opis może mieć maksymalnie 500 znaków.");
}




if (strlen($description) > 500) {
    exit("Opis może mieć maksymalnie 500 znaków.");
}


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


foreach ($badWords as $bw) {
    $pattern = '/' . preg_quote($bw, '/') . '/i';
    $description = preg_replace($pattern, str_repeat('*', strlen($bw)), $description);
}


$description = strip_tags($description);


$words = explode(" ", $description);
$wordCounts = array_count_values($words);
foreach ($wordCounts as $word => $count) {
    if ($count >= 20) {
        exit("Wykryto spam w opisie.");
    }
}


$description = preg_replace('/(.)\\1{3,}/u', '$1$1$1', $description);

file_put_contents(
    __DIR__ . '/donos_log.txt',
    $sender_class . "\n",
    FILE_APPEND
);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../secure/PHPMailer/src/Exception.php';
require __DIR__ . '/../secure/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../secure/PHPMailer/src/SMTP.php';

require_once __DIR__ . '/../secure/config.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PASS;
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom(MAIL_USER, 'e-Donos');
    $mail->addAddress(MAIL_USER);

    $mail->isHTML(true);
    $mail->Subject = 'NOWY DONOS!';

    $mail->Body = "
        <h2>Nowy donos</h2>

        <b>Donoszący:</b> {$sender_display}<br>
        <b>Klasa:</b> {$sender_class}<br><br>

        <b>Donoszony:</b> {$target_name}<br>

        <b>Data zdarzenia:</b> {$data}<br><br>
        <b>Miejsce:</b> {$place}<br><br>

        <b>Opis:</b><br>
        {$description}<br><br>
    ";

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->send();

} catch (Exception $e) {
    exit("Błąd wysyłania maila: {$mail->ErrorInfo}");
}


// ----------------------
// Zapis do JSON
// ----------------------
$saveFile = __DIR__ . '/../secure/donosy1.json';

$dataArray = [];
if (file_exists($saveFile)) {
    $decoded = json_decode(file_get_contents($saveFile), true);
    if (is_array($decoded)) {
        $dataArray = $decoded;
    }
}

$entry = [
    'id' => uniqid(),
    'time' => time(),
    'sender' => $loggedUser,
    'sender_display' => $sender_display,
    'class' => $sender_class,
    'target' => $target_name,
    'date' => $data,
    'place' => $place,
    'description' => $description,
    'likes' => 0,
    'dislikes' => 0,
    'voted' => []
];

$dataArray[] = $entry;

file_put_contents($saveFile, json_encode($dataArray, JSON_PRETTY_PRINT));

header('Location: succes.html');
exit;

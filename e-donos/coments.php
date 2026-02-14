<?php
session_start();
if (!isset($_SESSION['user'])) exit;

$donosId = $_POST['donos_id'] ?? '';
$text = trim($_POST['comment_text'] ?? '');

if ($donosId === '' || $text === '') exit;




if (strlen($text) > 300) {
    exit("Komentarz może mieć maksymalnie 300 znaków.");
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
    $text = preg_replace($pattern, str_repeat('*', strlen($bw)), $text);
}

$text = strip_tags($text);


$words = explode(" ", $text);
$wordCounts = array_count_values($words);
foreach ($wordCounts as $word => $count) {
    if ($count >= 20) {
        exit("Wykryto spam w komentarzu.");
    }
}


$text = preg_replace('/(.)\\1{3,}/u', '$1$1$1', $text);




$file = __DIR__ . '/../secure/donosy1.json';
$data = json_decode(file_get_contents($file), true);

foreach ($data as &$d) {
    if ($d['id'] === $donosId) {
        $d['comments'][] = [
            'id'       => uniqid('c_'),
            'author'   => $_SESSION['user'],
            'text'     => $text,
            'time'     => time(),
            'likes'    => 0,
            'dislikes' => 0
        ];
        break;
    }
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header("Location: donosy.php");
exit;

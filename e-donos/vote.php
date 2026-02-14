<?php
session_start();
if (!isset($_SESSION['user'])) {
    exit("Brak dostępu");
}

$user = $_SESSION['user']; // locked-in username
$type = $_GET['type'];     // like / dislike
$id   = $_GET['id'];       // unique ID

$file = __DIR__ . '/../secure/donosy1.json';
$data = json_decode(file_get_contents($file), true);

// find donos by uniqid
$index = array_search($id, array_column($data, 'id'));

if ($index === false) {
    exit("Donos nie istnieje");
}

$donos = &$data[$index];

// ensure voted array exists
if (!isset($donos['voted'])) {
    $donos['voted'] = [];
}

// if user already voted
if (isset($donos['voted'][$user])) {

    // if clicking same vote → do nothing
    if ($donos['voted'][$user] === $type) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        header("Location: donosy.php");
        exit;
    }

    // remove previous vote
    if ($donos['voted'][$user] === 'like') {
        $donos['likes']--;
    } else {
        $donos['dislikes']--;
    }
}

// add new vote
if ($type === 'like') {
    $donos['likes']++;
} else {
    $donos['dislikes']++;
}

// save user vote
$donos['voted'][$user] = $type;

// save file
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

header("Location: donosy.php");
exit;

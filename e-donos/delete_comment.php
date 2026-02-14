<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: donosy.php");
    exit;
}

$donosId   = $_POST['donos_id'] ?? '';
$commentId = $_POST['comment_id'] ?? '';

if ($donosId === '' || $commentId === '') {
    header("Location: donosy.php");
    exit;
}

$file = __DIR__ . '/../secure/donosy1.json';
$data = json_decode(file_get_contents($file), true);

foreach ($data as &$d) {
    if ($d['id'] !== $donosId) continue;

    if (!isset($d['comments'])) continue;

    foreach ($d['comments'] as $index => $c) {
        if ($c['id'] === $commentId && $c['author'] === $_SESSION['user']) {
            unset($d['comments'][$index]);
            $d['comments'] = array_values($d['comments']); // reindex
            break;
        }
    }

    break;
}
unset($d);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: donosy.php");
exit;

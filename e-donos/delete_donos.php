<?php
session_start();
if (!isset($_SESSION['user'])) {
    exit("Brak dostępu");
}

$user = $_SESSION['user'];
$id = $_GET['id'];

$file = __DIR__ . '/../secure/donosy1.json';
$data = json_decode(file_get_contents($file), true);

// znajdź donos po ID
$index = array_search($id, array_column($data, 'id'));

if ($index === false) {
    exit("Donos nie istnieje");
}

// upewnij się, że użytkownik usuwa SWÓJ donos
if ($data[$index]['sender'] !== $user) {
    exit("Nie możesz usuwać cudzych donosów");
}

// zapamiętaj klasę do usunięcia z logu
$classToRemove = $data[$index]['class'] ?? null;

// usuń donos z JSON
unset($data[$index]);
file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT));

// -----------------------------
// USUWANIE WPISU Z donos_log.txt
// -----------------------------
$logFile = __DIR__ . '/donos_log.txt';

if ($classToRemove !== null && file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES);

    $removed = false;
    $newLines = [];

    foreach ($lines as $line) {
        if (!$removed && trim($line) === trim($classToRemove)) {
            $removed = true;
            continue;
        }
        $newLines[] = $line;
    }

    file_put_contents($logFile, implode("\n", $newLines) . "\n");
}


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: my_donos.php?deleted=1");
exit;

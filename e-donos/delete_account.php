<?php
session_start();
header("Content-Type: text/plain");

if (!isset($_SESSION['user'])) {
    echo "Musisz być zalogowany.";
    exit;
}

$login = $_SESSION['user'];

// Wczytaj użytkowników
$usersFile = __DIR__ . '/../secure/users.json';
$users = json_decode(file_get_contents($usersFile), true);

if (!isset($users[$login])) {
    echo "Błąd: użytkownik nie istnieje.";
    exit;
}

$userClass = $users[$login]['class'] ?? null;

$donosFile = __DIR__ . '/../secure/donosy1.json';
$userDonosCount = 0;

if (file_exists($donosFile)) {
    $donosy = json_decode(file_get_contents($donosFile), true);

    foreach ($donosy as $donos) {
        if ($donos['sender'] === $login) {
            $userDonosCount++;
        }
    }
}


unset($users[$login]);
file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));



if (file_exists($donosFile)) {
    $noweDonosy = [];

    foreach ($donosy as $donos) {

        
        if ($donos['sender'] === $login) {
            continue;
        }

        
        if (isset($donos['comments']) && is_array($donos['comments'])) {
            $donos['comments'] = array_filter(
                $donos['comments'],
                fn($kom) => $kom['author'] !== $login
            );
        }

        $noweDonosy[] = $donos;
    }

    file_put_contents($donosFile, json_encode($noweDonosy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}



$logFile = __DIR__ . '/../donos_log.txt';


if ($userClass && file_exists($logFile) && $userDonosCount > 0) {

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];
    $toRemove = $userDonosCount;

    foreach ($lines as $line) {

        
        if (trim($line) === $userClass && $toRemove > 0) {
            $toRemove--;
            continue; 
        }

        
        $newLines[] = $line;
    }

    file_put_contents($logFile, implode(PHP_EOL, $newLines) . PHP_EOL);
}

session_destroy();

echo "SUCCESS";

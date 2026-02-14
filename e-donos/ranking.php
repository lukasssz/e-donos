<?php

$logFile = __DIR__ . '/donos_log.txt';

if (!file_exists($logFile)) {
    die("Brak danych — plik donos_log.txt nie istnieje.");
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$count = [];

foreach ($lines as $klasa) {
    $klasa = trim($klasa);

    if ($klasa === '') continue;

    if (!isset($count[$klasa])) {
        $count[$klasa] = 0;
    }

    $count[$klasa]++;
}

arsort($count);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ranking donosów</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 3vh 2vw;
            background: #f5f5f5;
        }

        h1 {
            text-align: center;
            margin-bottom: 4vh;
            font-size: 3.5vh;
        }

        .chart-container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 2vh 2vw;
            border-radius: 1.5vh;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        #donosChart {
            height: 45vh !important;
        }

        .powrot {
            padding: 1.8vh 3vw;
            font-size: 2.2vh;
            background: #1a2333;
            color: white;
            border: none;
            border-radius: 1vh;
            box-shadow: 0 0.8vh 2vh rgba(0,0,0,0.15);
            cursor: pointer;
            transition: 0.25s ease;
            position: fixed;
            bottom: 3vh;
            left: 50%;
            transform: translateX(-50%);
        }

        /* ⭐ MOBILE BOOST — ogromny wykres + duży przycisk */
        @media (max-width: 600px) {

            body {
                padding: 2vh 2vw;
            }

            h1 {
                font-size: 4.5vh;
                margin-bottom: 3vh;
            }

            .chart-container {
                width: 100%;
                padding: 4vh 4vw;
                border-radius: 2vh;
            }

            #donosChart {
                height: 70vh !important; /* DUŻY wykres */
            }

            .powrot {
                padding: 2.8vh 10vw;
                font-size: 3.2vh;
                border-radius: 2vh;
                bottom: 2vh; /* wyżej, żeby nie znikał */
            }
        }
    </style>
</head>

<body>

<button class="powrot" onclick="location.href='edonos.php'">Powrót</button>

<h1>Ranking donosów według klasy</h1>

<div class="chart-container">
    <canvas id="donosChart"></canvas>
</div>

<script>

const labels = <?php echo json_encode(array_keys($count)); ?>;
const data = <?php echo json_encode(array_values($count)); ?>;

new Chart(document.getElementById('donosChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Liczba donosów',
            data: data,
            backgroundColor: 'rgba(26, 35, 51, 0.7)',
            borderColor: 'rgba(26, 35, 51, 1)',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

</body>
</html>
